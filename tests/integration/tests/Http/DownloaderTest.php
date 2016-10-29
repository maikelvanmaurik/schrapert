<?php
namespace Schrapert\Test\Integration\Http;

use Clue\React\Block;
use React\Promise\Deferred;
use Schrapert\Http\ReadableBodyStream;
use Schrapert\Http\Request;
use Schrapert\Test\Integration\TestCase;

class DownloaderTest extends TestCase
{
    /**
     * @var \Schrapert\Http\Downloader\Downloader
     */
    private $downloader;

    private $eventLoop;

    public function setUp()
    {
        $this->downloader = $this->getContainer()->get('downloader');
        $this->eventLoop = $this->getContainer()->get('event_loop');
        return parent::setUp();
    }

    /**
     * @expectedException \Schrapert\Http\Downloader\Exception\DownloaderTimeoutException
     */
    public function testDownloadTimeouts()
    {
        $request = (new Request('http://timeout.schrapert.dev/?delay=100'))
            ->withMetaData('download_timeout', 2);

        $promise = $this->downloader->download($request)->otherwise(function($e) {
            throw $e;
        });

        await($promise, $this->eventLoop, 10);
    }

    public function testBasicStreamingDownloadIsWorking()
    {
        $request = (new Request('http://stream.schrapert.dev/big.php'))
            ->withMetaData('streaming', true)
            ->withMetaData('obey_success_code', false);

        $chunks = [];

        $promise = $this->downloader
            ->download($request)
            ->then(function($response) use (&$chunks, &$streamingPromise) {
                $deferred = new Deferred();
                if($response->getBody() instanceof ReadableBodyStream) {
                    $response->getBody()->on('data', function($data) use (&$chunks) {
                        $chunks[] = (string)$data;
                    });
                    $response->getBody()->on('end', function() use ($deferred) {
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

    public function testBasicNonStreamingDownloadIsWorking()
    {
        $request = new Request('http://blog.schrapert.dev');

        $html = '';

        $promise = $this->downloader
            ->download($request)
            ->then(function($response) use (&$html) {
                $html = (string)$response->getBody();
            });

        await($promise, $this->eventLoop, 10);

        $this->assertContains('Welcome to my blog!', $html);
    }

}