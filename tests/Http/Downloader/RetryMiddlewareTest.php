<?php
namespace Schrapert\Tests\Integration\Http\Downloader;

use Schrapert\Downloading\Downloader;
use Schrapert\Downloading\Middleware\RetryMiddleware;
use Schrapert\Http\Request;
use Schrapert\Tests\TestCase;

class RetryMiddlewareTest extends TestCase
{
    private $eventLoop;
    /**
     * @var Downloader
     */
    private $downloader;
    /**
     * @var RetryMiddleware
     */
    private $middleware;

    public function setUp(): void
    {
        $this->eventLoop = $this->getContainer()->get('event_loop');
        $this->downloader = $this->getContainer()->get('downloader');
        $this->middleware = $this->getContainer()->get('downloader_retry_middleware');
        parent::setUp();
    }

    /**
     * When the Cache-Control header contains a no-store directive the response should not be cached
     */
    public function testRequestsKeepAreBeingRetriedUntilSuccess()
    {
        return;
        $uri = sprintf('http://webshop.schrapert.dev/start-maintenance.php?duration=3');
        $request = new Request($uri, 'GET');
        $downloader = $this->downloader->withMiddleware($this->middleware);

        await($downloader->download($request), $this->eventLoop, 10);

        $uri = sprintf('http://webshop.schrapert.dev/products.php');
        $request = $request = new Request($uri, 'GET');
        await($downloader->download($request), $this->eventLoop, 20);
    }
}
