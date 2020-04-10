<?php

namespace Schrapert\Tests\Integration\Http\Downloader;

use Schrapert\Http\Downloader\Downloader;
use Schrapert\Http\Downloader\Middleware\DefaultHeadersMiddleware;
use Schrapert\Http\Request;
use Schrapert\Http\ResponseInterface;
use Schrapert\Tests\TestCase;

class DefaultHeadersMiddlewareTest extends TestCase
{
    private $eventLoop;
    /**
     * @var Downloader
     */
    private $downloader;
    /**
     * @var DefaultHeadersMiddleware
     */
    private $middleware;

    public function setUp(): void
    {
        $this->eventLoop = $this->getContainer()->get('event_loop');
        $this->downloader = $this->getContainer()->get('downloader');
        $this->middleware = $this->getContainer()->get('downloader_middleware_default_headers');
        parent::setUp();
    }

    public function testDefaultHeadersAreSet()
    {
        $downloader = $this->downloader->withMiddleware(
            $this->middleware->withHeaders([
                'FOO' => 'bar',
                'BAR' => 'baz',
            ]));

        $request = new Request('http://headers.schrapert.dev/request.php');

        $content = await(
            $downloader
                ->download($request)
                ->then(function (ResponseInterface $response) {
                    return (string) $response->getBody();
                }), $this->eventLoop, 10);

        $returnedHeaders = json_decode($content, true);

        $this->assertArrayHasKey('FOO', $returnedHeaders);
        $this->assertArrayHasKey('BAR', $returnedHeaders);
        $this->assertEquals('bar', $returnedHeaders['FOO']);
        $this->assertEquals('baz', $returnedHeaders['BAR']);
    }

    public function testExistingHeadersAreNotOverwritten()
    {
        return;

        $downloader = $this->downloader->withMiddleware(
            $this->middleware->withHeaders([
                'User-Agent' => 'Middleware',
            ]));

        $request = (new Request('http://headers.schrapert.dev/request.php'))
            ->withHeader('User-Agent', 'Request');

        $content = await(
            $downloader
                ->download($request)
                ->then(function (ResponseInterface $response) {
                    return (string) $response->getBody();
                }), $this->eventLoop, 10);

        $returnedHeaders = json_decode($content, true);

        $this->assertEquals('Request', @$returnedHeaders['User-Agent']);
    }
}
