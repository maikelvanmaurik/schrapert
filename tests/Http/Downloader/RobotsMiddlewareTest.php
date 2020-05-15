<?php
namespace Schrapert\Tests\Integration\Http\Downloader;

use Mockery as m;
use Schrapert\Crawling\Exceptions\IgnoreRequestException;
use Schrapert\DI\DefaultContainer;
use Schrapert\Downloading;
use Schrapert\Downloading\Middleware\RobotsTxtDownloaderMiddleware;
use Schrapert\Events\EventDispatcher;
use Schrapert\Http\Request;
use Schrapert\Pipeline\PipelineBuilder;
use Schrapert\Tests\TestCase;

class RobotsMiddlewareTest extends TestCase
{
    private $eventLoop;
    /**
     * @var Downloader
     */
    private $downloader;

    public function setUp(): void
    {
        $this->eventLoop = $this->getContainer()->get('event_loop');
        parent::setUp();
    }

    protected function getDownloader()
    {
        $transactionFactory = m::mock(Downloading\TransactionFactory::class);
        $transactionFactory
            ->shouldReceive('createTransaction')
            ->andReturnUsing(function ($request) {
                var_dump(get_class($request));
            });
        return new Downloader(
            m::mock(EventDispatcher::class),
            new PipelineBuilder(new DefaultContainer()),
            $transactionFactory
        );
    }

    /**
     * @expectedException IgnoreRequestException
     */
    public function testDoesNotAllowBlockedBots()
    {
        $downloader = $this->getDownloader()->withMiddleware(RobotsTxtDownloaderMiddleware::class);

        $request = (new Request('http://robotstxt.schrapert.dev/disallowed-for-bad-bot/'))
            ->withHeader('User-Agent', 'BadBot');

        $response = await($downloader->download($request));
    }
}
