<?php
namespace Schrapert\Test\Integration\Http\Downloader;

use Schrapert\Event\EventDispatcherInterface;
use Schrapert\Http\Downloader\Downloader;
use Schrapert\Http\Downloader\Middleware\Event\ConcurrentRequestLimitSlotsExceededEvent;
use Schrapert\Http\Request;
use Schrapert\Http\RequestInterface;
use Schrapert\Http\ResponseInterface;
use Schrapert\Test\Integration\TestCase;
use Schrapert\Http\Downloader\Middleware\ConcurrentRequestLimitMiddleware;

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

    public function setUp()
    {
        $this->eventLoop = $this->getContainer()->get('event_loop');
        $this->events = $this->getContainer()->get('event_dispatcher');
        $this->downloader = $this->getContainer()->get('downloader');
        $this->middleware = $this->getContainer()->get('downloader_middleware_concurrent_request_limit');
        parent::setUp();
    }

    public function testConcurrentRequestsPerDomainAreLimited()
    {
        $events = clone $this->events;

        $secondRequestIsDeferred = false;
        $usedDelay = null;

        $events->addListener('concurrent-request-limit-slots-exceeded', function(ConcurrentRequestLimitSlotsExceededEvent $e) use (&$secondRequestIsDeferred, &$usedDelay) {
            $request = $e->getRequest();
            if('http://webshop.schrapert.dev' == (string)$request->getUri()) {
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
            ->then(function(ResponseInterface $response) {
                return time();
            });

        $promiseB = $downloader->download($requestB)
            ->then(function(ResponseInterface $response) {
                return time();
            });

        $responses = await(\React\Promise\all([$promiseA, $promiseB]), $this->eventLoop, 10);

        $this->assertEquals(1, $usedDelay);
        $this->assertTrue($secondRequestIsDeferred);
        $this->assertGreaterThanOrEqual(1, $responses[1] - $responses[0], 'There should be at least a second between the responses, since they were delayed 1 second');
    }

    public function testGzipIsWorkingWithNonStreamingRequests()
    {
        $compressionMiddleware = $this->getContainer()->get('downloader_middleware_compression');

        $downloader = $this->downloader->withMiddleware($compressionMiddleware);

        $request = new Request('http://compression.schrapert.dev');

        $headers = [];

        $promise = $downloader->download($request)
            ->then(function(ResponseInterface $response) use (&$headers) {
                $headers = $response->getHeaders();
                return (string)$response->getBody();
            });

        $content = await($promise, $this->eventLoop, 10);
        $this->assertContains('This content is compressed using gzip encoding.', $content);
        $this->assertArrayHasKey('Content-Encoding', $headers);
        $this->assertEquals('gzip', reset($headers['Content-Encoding']));
    }
}