<?php

namespace Schrapert\Downloading;

use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use Schrapert\Downloading\Events\DownloadRequestEvent;
use Schrapert\Downloading\Events\ResponseDownloadedEvent;
use Schrapert\Downloading\Exception\TransactionTimeoutException;
use Schrapert\Downloading\Exceptions\DownloaderTimeoutException;
use Schrapert\Events\EventDispatcherInterface;
use Schrapert\Pipeline\PipelineBuilderInterface;
use Schrapert\Pipeline\PipelineInterface;

use function React\Promise\resolve;

class Downloader implements DownloaderInterface
{
    private $queue;

    private $transferring;

    private $logger;

    private $middleware;

    private $handlerFactory;

    private $events;
    /**
     * @var PipelineBuilderInterface
     */
    private $pipelineBuilder;

    public function __construct(
        EventDispatcherInterface $events,
        PipelineBuilderInterface $pipelineBuilder,
        HandlerFactoryInterface $handlerFactory,
        array $middleware = []
    ) {
        $this->events = $events;
        $this->middleware = [];
        $this->pipelineBuilder = $pipelineBuilder;
        $this->setMiddleware($middleware);
        $this->handlerFactory = $handlerFactory;
        $this->queue = [];
        $this->transferring = [];
    }

    private function removeTransferred(RequestInterface $request)
    {
        $index = array_search($request, $this->transferring, true);
        unset($this->transferring[$index]);
    }

    private function enqueueRequest()
    {
        // $this->logger->debug('Enqueue download request {uri}', ['uri' => $request->getUri()]);
        return function (RequestInterface $request, RequestOptionsInterface $options) {
            $deferred = new Deferred();

            $this->queue[] = [$request, $deferred];

            $this->processQueue();

            return $deferred->promise();
        };
    }

    private function processQueue()
    {
        while (count($this->queue) > 0) {
            list($request, $deferred) = array_shift($this->queue);

            // $this->logger->debug('Process en-queued download request {uri}', ['uri' => (string) $request->getUri()]);
            $download = $this->fetch($request);
            $download->then(function ($response) use ($deferred, $request) {
                if ($response instanceof ResponseInterface) {
                    $response = $response->withMetaData('request', $request);
                }
                $deferred->resolve($response);
                return $response;
            }, function ($error) use ($deferred) {
                $deferred->reject($error);
                return $error;
            });
            $download->always(function () use ($request) {
                $this->removeTransferred($request);
            });
        }
    }

    public function fetch(RequestInterface $request): PromiseInterface
    {
        $this->transferring[] = $request;

        $this->events->dispatch(new DownloadRequestEvent($request, date_create()));

        $handler = $this
            ->handlerFactory
            ->createHandler($request);

        return $handler->download($request)->then(null, function (\Exception $e) use ($request) {
            if ($e instanceof TransactionTimeoutException) {
                $e = new DownloaderTimeoutException($this, $request);
            }
            throw $e;
        });
    }

    public function withMiddleware($middleware)
    {
        $this->middleware[] = $middleware;
        return $this;
    }

    public function withoutMiddleware($middleware)
    {
        foreach ($this->middleware as $item) {
            if ($item !== $middleware) {
                $this->middleware[] = $item;
            }
        }
        return $this;
    }

    public function hasMiddleware($middleware)
    {
        return false !== array_search($middleware, $this->middleware, true);
    }

    public function getMiddleware()
    {
        return $this->middleware;
    }

    private function setMiddleware($middleware)
    {
        $this->middleware = (array) $middleware;
    }

    private function makePipeline(RequestInterface $request, RequestOptionsInterface $options): PipelineInterface
    {
        $middleware = $options->get('middleware', $this->middleware);
        return $this->pipelineBuilder->build($middleware);
    }

    public function download(RequestInterface $request, ?RequestOptionsInterface $options = null): PromiseInterface
    {
        if (null === $options) {
            $options = new RequestOptions();
        }
        return resolve($this->makePipeline($request, $options)->run($this->enqueueRequest(), $request, $options));
    }
}
