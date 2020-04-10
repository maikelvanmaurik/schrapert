<?php

namespace Schrapert\Tests\Integration\Http;

use React\Promise\Deferred;
use Schrapert\Event\EventDispatcherInterface;
use Schrapert\Http\Downloader\Event\DownloadCompleteEvent;
use Schrapert\Http\ReadableBodyStream;
use Schrapert\Http\Request;
use Schrapert\Tests\TestCase;

class DownloaderTest extends TestCase
{
    /**
     * @var \Schrapert\Http\Downloader\Downloader
     */
    private $downloader;

    private $eventLoop;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function setUp(): void
    {
        $this->downloader = $this->getContainer()->get('downloader');
        $this->eventLoop = $this->getContainer()->get('event_loop');
        $this->eventDispatcher = $this->getContainer()->get('event_dispatcher');
        parent::setUp();
    }

    /**
     * @expectedException \Schrapert\Http\Downloader\Exception\DownloaderTimeoutException
     */
    public function testDownloadTimeouts()
    {
        $request = (new Request('http://timeout.schrapert.dev/?delay=100'))
            ->withMetadata('download_timeout', 2);

        $promise = $this->downloader->download($request)->otherwise(function ($e) {
            throw $e;
        });

        await($promise, $this->eventLoop, 10);
    }

    public function testBasicStreamingDownloadIsWorking()
    {
        $request = (new Request('http://stream.schrapert.dev/big.php'))
            ->withMetadata('streaming', true)
            ->withMetadata('obey_success_code', false);

        $chunks = [];

        $promise = $this->downloader
            ->download($request)
            ->then(function ($response) use (&$chunks, &$streamingPromise) {
                $deferred = new Deferred();
                if ($response->getBody() instanceof ReadableBodyStream) {
                    $response->getBody()->on('data', function ($data) use (&$chunks) {
                        $chunks[] = (string) $data;
                    });
                    $response->getBody()->on('end', function () use ($deferred) {
                        $deferred->resolve();
                    });
                }

                return $deferred->promise();
            });

        await($promise, $this->eventLoop, 10);

        $html = implode('', $chunks);

        $this->assertGreaterThan(1, count($chunks));
        $this->assertEquals(1000000, strlen($html));
    }

    public function testDownloaderDispatchesEvents()
    {
        return;

        $request = new Request('http://test-web-server');

        $complete = false;

        $this->eventDispatcher->addListener('response-downloaded', function (DownloadCompleteEvent $e) use (&$complete) {
            $complete = true;
        });

        await($this->downloader->download($request), $this->eventLoop, 10);

        $this->assertTrue($complete);
    }

    public function testBasicNonStreamingDownloadIsWorking()
    {
        $request = new Request('http://test-web-server');

        $html = '';

        $promise = $this->downloader
            ->download($request)
            ->then(function ($response) use (&$html) {
                $html = (string) $response->getBody();
            });

        await($promise, $this->eventLoop, 10);

        $this->assertContains('Welcome to my blog!', $html);
    }
}
