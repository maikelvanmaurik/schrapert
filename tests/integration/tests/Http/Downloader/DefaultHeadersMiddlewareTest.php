<?php
namespace Schrapert\Test\Integration\Http\Downloader;

use Schrapert\Http\Cookies\SetCookie;
use Schrapert\Http\Downloader\Downloader;
use Schrapert\Http\Request;
use Schrapert\Http\ResponseInterface;
use Schrapert\Test\Integration\TestCase;
use Schrapert\Http\Downloader\Middleware\DefaultHeadersMiddleware;

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

    public function setUp()
    {
        $this->eventLoop = $this->getContainer()->get('event_loop');
        $this->downloader = $this->getContainer()->get('downloader');
        $this->middleware = $this->getContainer()->get('downloader_middleware_default_headers');
        return parent::setUp();
    }

    public function testDefaultHeadersAreSet()
    {
        $downloader = $this->downloader->withMiddleware(
            $this->middleware->withHeaders([
                'foo' => 'bar',
                'bar' => 'baz'
            ]));

        $request = new Request('http://headers.schrapert.dev/request.php');

        $content = await(
            $downloader
                ->download($request)
                ->then(function(ResponseInterface $response) {
                    return (string)$response->getBody();
            }), $this->eventLoop, 10);

        var_dump($content);

        $returnedHeaders = json_decode($content, true);

        $this->assertArrayHasKey('foo', $returnedHeaders);
        $this->assertArrayHasKey('bar', $returnedHeaders);
        $this->assertEquals('bar', $returnedHeaders['foo']);
        $this->assertEquals('baz', $returnedHeaders['bar']);
    }

    public function testExistingHeadersAreNotOverwritten()
    {
        return;
        $downloader = $this->downloader->withMiddleware(
            $this->middleware->withHeaders([
                'User-Agent' => 'Middleware'
            ]));

        $request = (new Request('http://headers.schrapert.dev/request.php'))
            ->withHeader('User-Agent', 'Request');

        $content = await(
            $downloader
                ->download($request)
                ->then(function(ResponseInterface $response) {
                    return (string)$response->getBody();
                }), $this->eventLoop, 10);

        $returnedHeaders = json_decode($content, true);

        $this->assertEquals('Request', $returnedHeaders['User-Agent']);
    }
}