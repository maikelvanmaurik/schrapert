<?php
namespace Schrapert\Tests\Integration\Http\Downloader;

use Schrapert\Http\Cache\DummyPolicy;
use Schrapert\Http\Cache\FileStorage;
use Schrapert\Http\Cache\Rfc2616Policy;
use Schrapert\Http\Downloader\Downloader;
use Schrapert\Http\Downloader\Middleware\HttpCacheMiddleware;
use Schrapert\Http\Request;
use Schrapert\Http\RequestInterface;
use Schrapert\Http\ResponseInterface;
use Schrapert\Tests\TestCase;

class HttpCacheMiddlewareTest extends TestCase
{
    private $eventLoop;
    /**
     * @var Downloader
     */
    private $downloader;
    /**
     * @var HttpCacheMiddleware
     */
    private $middleware;
    /**
     * @var FileStorage
     */
    private $fileStorage;
    /**
     * @var RFC2616Policy
     */
    private $rfc2616Policy;
    /**
     * @var DummyPolicy
     */
    private $dummyPolicy;
    /**
     * @var DatabaseStorage
     */
    private $databaseStorage;

    public function setUp(): void
    {
        $this->eventLoop = $this->getContainer()->get('event_loop');
        $this->downloader = $this->getContainer()->get('downloader');
        $this->middleware = $this->getContainer()->get('downloader_middleware_http_cache');
        $this->fileStorage = $this->getContainer()->get('http_cache_file_storage');
        $cacheDir = str_replace('\\', '/', ETC_DIR.'cache/httpcache/' . __CLASS__);
        $this->fileStorage = $this->fileStorage->withCacheDirectory($cacheDir);
        $this->databaseStorage = $this->getContainer()->get('http_cache_database_storage');
        $this->rfc2616Policy = $this->getContainer()->get('http_cache_rfc2616_policy');
        $this->dummyPolicy = $this->getContainer()->get('http_cache_dummy_policy');
        parent::setUp();
    }

    public function testResponsesWithCacheControlMaxAgeHeaderAreBeingCachedWhenUsingDatabaseStorageIcwRfc2616Policy()
    {

    }

    /**
     * When the Cache-Control header contains a no-store directive the response should not be cached
     */
    public function testResponsesOfARequestWithACacheControlNoStoreHeaderAreNotBeingCachedWhenUsingRfc2616Policy()
    {
        $uri = sprintf('http://caching.schrapert.dev/cache-control-header.php?header-value=%s', urlencode('no-store'));
        $request = new Request($uri, 'GET');

        $fileStorage = $this->fileStorage;
        $fileStorage->clear();

        $middleware = $this->middleware
            ->withPolicy($this->rfc2616Policy)
            ->withStorage($fileStorage);

        $downloader = $this->downloader->withMiddleware($middleware);

        $current = time();
        $a = await(
            $downloader
                ->download($request)
                ->then(function(ResponseInterface $response) {
                    return (string)$response->getBody();
                }), $this->eventLoop, 10);

        if(time() == $current) {
            sleep(1);
        }

        $b = await(
            $downloader
                ->download($request)
                ->then(function(ResponseInterface $response) {
                    return (string)$response->getBody();
                }), $this->eventLoop, 10);

        $this->assertNotEquals($a, $b);

        $fileStorage->clear();
    }

    public function testResponsesWithCacheControlNoCacheAreNotBeingCachedWhenUsingFileStorageAndRfc2616Policy()
    {
        $request = new Request('http://caching.schrapert.dev/no-cache.php');

        $fileStorage = $this->fileStorage;
        $fileStorage->clear();

        $middleware = $this->middleware
            ->withPolicy($this->rfc2616Policy)
            ->withStorage($fileStorage);

        $downloader = $this->downloader->withMiddleware($middleware);

        $current = time();
        $a = await(
            $downloader
                ->download($request)
                ->then(function(ResponseInterface $response) {
                    return (string)$response->getBody();
                }), $this->eventLoop, 10);

        if(time() == $current) {
            sleep(1);
        }

        $b = await(
            $downloader
                ->download($request)
                ->then(function(ResponseInterface $response) {
                    return (string)$response->getBody();
                }), $this->eventLoop, 10);

        $this->assertNotEquals($a, $b);

        $fileStorage->clear();
    }

    public function testContentIsBeingCachedWithMaxAgeResponseHeaderWithFileStorageAndDummyPolicy()
    {
        $request = new Request('http://caching.schrapert.dev/max-age.php?max-age=20');

        $fileStorage = $this->fileStorage;
        $fileStorage->clear();

        $middleware = $this->middleware
            ->withPolicy($this->dummyPolicy)
            ->withStorage($fileStorage);

        $downloader = $this->downloader->withMiddleware($middleware);

        $current = time();
        $a = await(
            $downloader
                ->download($request)
                ->then(function(ResponseInterface $response) {
                    return (string)$response->getBody();
                }), $this->eventLoop, 3);

        if(time() == $current) {
            sleep(1);
        }

        $b = await(
            $downloader
                ->download($request)
                ->then(function(ResponseInterface $response) {
                    return (string)$response->getBody();
                }), $this->eventLoop, 3);

        $this->assertEquals($a, $b);

        $fileStorage->clear();
    }

    public function testContentCacheIsBeingCheckedOnFreshnessWhenUsingFileStorageIcwRfc2616Policy()
    {
        return;

        $uri = sprintf('http://caching.schrapert.dev/cache-control-header.php?header-value=%s', urlencode('public,max-age=1'));
        $request = new Request($uri);

        $fileStorage = $this->fileStorage;
        $fileStorage->clear();

        $middleware = $this->middleware
            ->withPolicy($this->rfc2616Policy)
            ->withStorage($fileStorage);

        $downloader = $this->downloader->withMiddleware($middleware);

        $current = time();
        $a = await(
            $downloader
                ->download($request)
                ->then(function(ResponseInterface $response) {
                    return (string)$response->getBody();
                }), $this->eventLoop, 3);

        if(time() == $current) {
            sleep(1);
        }

        $b = await(
            $downloader
                ->download($request)
                ->then(function(ResponseInterface $response) {
                    return (string)$response->getBody();
                }), $this->eventLoop, 3);

        $this->assertEquals($a, $b);

        $fileStorage->clear();
    }


    public function testStaleContentCacheIsBeingCheckedOnFreshnessWhenUsingFileStorageIcwRfc2616Policy()
    {
        $eTag = 'X2SVW';
        $uri = sprintf('http://caching.schrapert.dev/etag.php?cc[max-age]=%s&etag=%s', 10, $eTag);
        $request = new Request($uri);

        $fileStorage = $this->fileStorage;
        $fileStorage->clear();

        $middleware = $this->middleware
            ->withPolicy($this->rfc2616Policy)
            ->withStorage($fileStorage);

        $downloader = $this->downloader->withMiddleware($middleware);

        $current = time();
        $a = await(
            $downloader
                ->download($request)
                ->then(function(ResponseInterface $response) {
                    /* @var $request RequestInterface */
                    $request = $response->getMetadata('request');
                    // First request so it should not have the If-None-Match header
                    $this->assertFalse($request->hasHeader('If-None-Match'));
                    return (string)$response->getBody();
                }), $this->eventLoop, 10);

        if(time() == $current) {
            sleep(1);
        }

        $b = await(
            $downloader
                ->download($request)
                ->then(function(ResponseInterface $response) use ($eTag) {
                    /* @var $request RequestInterface */
                    $request = $response->getMetadata('request');
                    // Second request so it should have the If-None-Match header
                    $this->assertEquals($eTag, $request->getHeaderLine('If-None-Match'));
                    return (string)$response->getBody();
                }), $this->eventLoop, 10);

        $this->assertEquals($a, $b);

        $fileStorage->clear();
    }
}