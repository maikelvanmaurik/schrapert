<?php
namespace Schrapert\Test\Unit\Http;

use Schrapert\Http\Request;
use Schrapert\Test\Unit\TestCase;

class DownloaderTest extends TestCase
{
    /**
     * @var \Schrapert\Http\Downloader\Downloader
     */
    private $downloader;

    public function setUp()
    {
        $this->downloader = $this->getContainer()->get('downloader');
        parent::setUp();
    }

    public function testDownloaderDoesNotContainAnyMiddlewareByDefault()
    {
        $middleware = $this->downloader->getMiddleware();
        $this->assertEmpty($middleware);
    }

    public function testCanAddMiddleware()
    {
        $downloader = $this->downloader;
        $middleware = $this->getContainer()->get('downloader_middleware_robotstxt');
        $this->assertFalse($downloader->hasMiddleware($middleware));

        $d2 = $downloader->withMiddleware($middleware);
        $this->assertTrue($d2->hasMiddleware($middleware));
    }

    public function testCanRemoveMiddleware()
    {
        $d = $this->downloader;
        
    }

    public function testDownloadReturnsPromise()
    {
        $request = new Request('http://webshop.schrapert.dev');
        $d = $this->downloader;
        $r = $d->download($request);

        $this->assertInstanceOf('React\Promise\PromiseInterface', $r);
    }
}