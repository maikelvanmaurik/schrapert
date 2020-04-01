<?php
namespace Schrapert\Test\Integration\Http\Downloader;

use Schrapert\Http\RequestInterface;
use Schrapert\Http\Downloader\Downloader;
use Schrapert\Http\Request;
use Schrapert\Http\ResponseInterface;
use Schrapert\Test\Integration\TestCase;
use Schrapert\Http\Downloader\Middleware\RetryMiddleware;
use Schrapert\Http\Cache\FileStorage;
use Schrapert\Http\Cache\Rfc2616Policy;
use Schrapert\Http\Cache\DummyPolicy;

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

    public function setUp()
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