<?php
namespace Schrapert\Tests\Integration\Http\Downloader;

use Schrapert\Downloading\Downloader;
use Schrapert\Downloading\Middleware\ConcurrentRequestLimitMiddleware;
use Schrapert\Downloading\Middleware\Event\ConcurrentRequestLimitSlotsExceededEvent;
use Schrapert\Events\EventDispatcherInterface;
use Schrapert\Http\Request;
use Schrapert\Http\ResponseInterface;
use Schrapert\Tests\TestCase;

class ConcurrentRequestLimitMiddlewareTest extends TestCase
{
    private $eventLoop;
    /**
     * @var Downloader
     */
    private $downloader;
    /**
     * @var ConcurrentRequestLimitMiddleware
     */
    private $middleware;
    /**
     * @var EventDispatcherInterface
     */
    private $events;

    public function setUp(): void
    {
        $this->eventLoop = $this->getContainer()->get('event_loop');
        $this->events = $this->getContainer()->get('event_dispatcher');
        $this->downloader = $this->getContainer()->get('downloader');
        $this->middleware = $this->getContainer()->get('downloader_middleware_concurrent_request_limit');
        parent::setUp();
    }

    public function testConcurrentRequestsPerSlotAreLimitedByDomain()
    {
        $events = clone $this->events;

        $secondRequestIsDeferred = false;
        $usedDelay = null;

        $events->addListener('concurrent-request-limit-slots-exceeded', function (ConcurrentRequestLimitSlotsExceededEvent $e) use (&$secondRequestIsDeferred, &$usedDelay) {
            $request = $e->getRequest();
            if ('http://webshop.schrapert.dev' == (string)$request->getUri()) {
                $secondRequestIsDeferred = true;
            }
            $usedDelay = $e->getDelay();
        });

        $middleware = $this->middleware
            ->withEventDispatcher($events)
            ->withPerSlotConcurrentRequests(1)
            ->withDelay(1)
            ->withTotalConcurrentRequests(2);

        $downloader = $this->downloader->withMiddleware($middleware);

        $requestA = (new Request('http://webshop.schrapert.dev/products.php'));

        $requestB = (new Request('http://webshop.schrapert.dev'));

        $promiseA = $downloader->download($requestA)
            ->then(function (ResponseInterface $response) {
                return time();
            });

        $promiseB = $downloader->download($requestB)
            ->then(function (ResponseInterface $response) {
                return time();
            });

        $responses = await(\React\Promise\all([$promiseA, $promiseB]), $this->eventLoop, 10);

        $this->assertEquals(1, $usedDelay);
        $this->assertTrue($secondRequestIsDeferred);
        $this->assertGreaterThanOrEqual(1, $responses[1] - $responses[0], 'There should be at least a second between the responses, since they were delayed 1 second');
    }

    public function testConcurrentRequestsAreDelayedWhenUsingTheTotalConcurrentRequestSetting()
    {
        return;


        $events = clone $this->events;

        $secondRequestIsDeferred = false;
        $usedDelay = null;

        $events->addListener('concurrent-request-limit-slots-exceeded', function (ConcurrentRequestLimitSlotsExceededEvent $e) use (&$secondRequestIsDeferred, &$usedDelay) {
            $request = $e->getRequest();
            if ('http://webshop.schrapert.dev' == (string)$request->getUri()) {
                $secondRequestIsDeferred = true;
            }
            $usedDelay = $e->getDelay();
        });

        $middleware = $this->middleware
            ->withEventDispatcher($events)
            ->withPerSlotConcurrentRequests(1)
            ->withDelay(1)
            ->withTotalConcurrentRequests(1);

        $downloader = $this->downloader->withMiddleware($middleware);

        $requestA = (new Request('http://webshop.schrapert.dev/products.php'));

        $requestB = (new Request('http://webshop.schrapert.dev'));

        $promiseA = $downloader->download($requestA)
            ->then(function (ResponseInterface $response) {
                return time();
            });

        $promiseB = $downloader->download($requestB)
            ->then(function (ResponseInterface $response) {
                return time();
            });

        $responses = await(\React\Promise\all([$promiseA, $promiseB]), $this->eventLoop, 10);

        $this->assertEquals(1, $usedDelay);
        $this->assertTrue($secondRequestIsDeferred);
        $this->assertGreaterThanOrEqual(1, $responses[1] - $responses[0], 'There should be at least a second between the responses, since they were delayed 1 second');
    }
}
