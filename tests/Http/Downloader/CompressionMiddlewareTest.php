<?php

namespace Schrapert\Tests\Integration\Http\Downloader;

use React\Promise\Deferred;
use Schrapert\Http\Downloader\Downloader;
use Schrapert\Http\Request;
use Schrapert\Http\ResponseInterface;
use Schrapert\Tests\TestCase;

class CompressionMiddlewareTest extends TestCase
{
    private $eventLoop;
    /**
     * @var Downloader
     */
    private $downloader;

    public function setUp(): void
    {
        $this->eventLoop = $this->getContainer()->get('event_loop');
        $this->downloader = $this->getContainer()->get('downloader');
        parent::setUp();
    }

    public function testGzipIsWorkingWithStreamingRequests()
    {
        return;
        $compressionMiddleware = $this->getContainer()->get('downloader_middleware_compression');

        $downloader = $this->downloader->withMiddleware($compressionMiddleware);

        $request = (new Request('http://compression.schrapert.dev'))
            ->withMetadata('streaming', true);

        $headers = [];

        $promise = $downloader->download($request)
            ->then(function (ResponseInterface $response) use (&$headers) {
                $headers = $response->getHeaders();
                $deferred = new Deferred();

                return $deferred->promise();
            });

        $content = await($promise, $this->eventLoop, 10);
        $this->assertContains('This content is compressed using gzip encoding.', $content);
        $this->assertArrayHasKey('Content-Encoding', $headers);
        $this->assertEquals('gzip', reset($headers['Content-Encoding']));
    }

    public function testGzipIsWorkingWithNonStreamingRequests()
    {
        $compressionMiddleware = $this->getContainer()->get('downloader_middleware_compression');

        $downloader = $this->downloader->withMiddleware($compressionMiddleware);

        $request = new Request('http://compression.schrapert.dev');

        $headers = [];

        $promise = $downloader->download($request)
            ->then(function (ResponseInterface $response) use (&$headers) {
                $headers = $response->getHeaders();

                return (string) $response->getBody();
            });

        $content = await($promise, $this->eventLoop, 10);
        $this->assertContains('This content is compressed using gzip encoding.', $content);
        $this->assertArrayHasKey('Content-Encoding', $headers);
        $this->assertEquals('gzip', reset($headers['Content-Encoding']));
    }
}
