<?php

namespace Schrapert\Downloading\Middleware;

use React\Dns\Resolver\Resolver;
use React\Promise\Deferred;
use React\Promise\FulfilledPromise;
use Schrapert\Downloading\Middleware\Event\ConcurrentRequestLimitSlotsExceededEvent;
use Schrapert\Downloading\Middleware\Event\ConcurrentRequestLimitTotalExceededEvent;
use Schrapert\Events\EventDispatcherInterface;
use Schrapert\Http\RequestInterface;
use Schrapert\Http\ResponseInterface;
use Schrapert\Log\LoggerInterface;
use Schrapert\Util\DelayedCallbackFactory;

class ConcurrentRequestLimitMiddleware implements DownloaderMiddlewareInterface, ProcessRequestMiddlewareInterface, ProcessResponseMiddlewareInterface
{
    private $delayedCallbackFactory;

    private $mode = self::DOMAIN_CONCURRENCY_MODE;

    private $perSlotConcurrentRequests = 10;

    private $totalConcurrentRequests = 10;

    private $numActive = 0;

    private $delay = 0;

    private $randomizeDelay = false;

    private $slots = [];

    const IP_CONCURRENCY_MODE = 1;

    const DOMAIN_CONCURRENCY_MODE = 2;

    public function __construct(
        EventDispatcherInterface $events,
        LoggerInterface $logger,
        DelayedCallbackFactory $delayedCallbackFactory,
        Resolver $dnsResolver
    ) {
        $this->events = $events;
        $this->logger = $logger;
        $this->dnsResolver = $dnsResolver;
        $this->delayedCallbackFactory = $delayedCallbackFactory;
    }

    public function withEventDispatcher(EventDispatcherInterface $events)
    {
        $new = clone $this;
        $new->events = $events;
        return $new;
    }

    public function withTotalConcurrentRequests($total)
    {
        $new = clone $this;
        $new->totalConcurrentRequests = intval($total);
        return $new;
    }

    public function withRandomizeDownloadDelay($bool)
    {
        $new = clone $this;
        $new->randomizeDownloadDelay = filter_var($bool, FILTER_VALIDATE_BOOLEAN);
        return $new;
    }

    public function withConcurrencyMode($mode)
    {
        $new = clone $this;
        $new->mode = $mode;
        return $new;
    }

    /**
     * @param int|float $delay
     * @return ConcurrentRequestLimitMiddleware
     */
    public function withDelay($delay)
    {
        $new = clone $this;
        $new->delay = $delay;
        return $new;
    }

    public function withPerSlotConcurrentRequests($concurrentRequests)
    {
        $new = clone $this;
        $new->perSlotConcurrentRequests = intval($concurrentRequests);
        return $new;
    }

    private function getSlotKey(RequestInterface $request)
    {
        $host = $request->getUri()->getHost();
        $deferred = new Deferred();
        switch ($this->mode) {
            case self::DOMAIN_CONCURRENCY_MODE:
                return new FulfilledPromise($host);
                break;
            case self::IP_CONCURRENCY_MODE:
                $this->dnsResolver->resolve($host)->then(function ($value) use ($deferred) {
                    $deferred->resolve($value);
                }, function ($e) use ($deferred) {
                    $deferred->reject($e);
                });
                break;
            default:
                $deferred->reject('Invalid mode');
                break;
        }

        return $deferred->promise();
    }

    private function getAwaitDelay()
    {
        if ($this->randomizeDelay) {
            return abs(rand(.5 * $this->delay, 1.5, $this->delay));
        }
        return $this->delay;
    }

    /**
     * @param $key
     * @param RequestInterface $request
     * @param Deferred $deferred
     * @triggers $event
     */
    public function awaitPossible($key, RequestInterface $request, Deferred $deferred)
    {
        if ($this->numActive >= $this->totalConcurrentRequests) {
            $delay = $this->getAwaitDelay();
            $this->events->dispatch(new ConcurrentRequestLimitTotalExceededEvent($request, $delay));
            $this->logger->debug(
                'Delay request {uri} (key: {key}, total requests exceeded',
                ['uri' => (string)$request->getUri(), 'key' => $key]
            );
            $this->delayedCallbackFactory->factory([$this, 'awaitPossible'], [$key, $request, $deferred])
                ->schedule($this->getAwaitDelay());
        } else {
            if (! array_key_exists($key, $this->slots)) {
                $this->slots[$key] = [];
            }

            if (count($this->slots[$key]) >= $this->perSlotConcurrentRequests) {
                $delay = $this->getAwaitDelay();
                $this->events->dispatch(new ConcurrentRequestLimitSlotsExceededEvent($request, $delay));
                $this->logger->debug(
                    'Delay request {uri} (key: {key}, slot requests exceeded',
                    ['uri' => (string)$request->getUri(), 'key' => $key]
                );
                $this->delayedCallbackFactory->factory([$this, 'awaitPossible'], [$key, $request, $deferred])
                    ->schedule($delay);
                return;
            }

            $this->slots[$key][] = $request;

            $this->numActive++;

            $deferred->resolve($request);
        }
    }

    /**
     * @param RequestInterface $request
     */
    public function processRequest(RequestInterface $request)
    {
        $deferred = new Deferred();

        $this->getSlotKey($request)->then(function ($key) use ($request, $deferred) {
            $this->awaitPossible($key, $request, $deferred);
        });

        return $deferred->promise();
    }

    public function processResponse(ResponseInterface $response, RequestInterface $request)
    {
        $this->getSlotKey($request)->then(function ($key) use ($request) {
            $index = array_search($request, $this->slots[$key]);
            unset($this->slots[$key][$index]);
            $this->numActive--;
        });
        return $response;
    }
}
