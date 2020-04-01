<?php
namespace Schrapert\Tests\Integration\Http\Downloader;

use Schrapert\Http\Downloader\Downloader;
use Schrapert\Http\Request;
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
        $this->downloader = $this->getContainer()->get('downloader');
        parent::setUp();
    }

    /**
     * @expectedException \Schrapert\Crawl\Exception\IgnoreRequestException
     */
    public function testDoesNotAllowBlockedBots()
    {
        $downloader = $this->downloader->withMiddleware($this->getContainer()->get('downloader_middleware_robots_txt'));

        $request = (new Request('http://robotstxt.schrapert.dev/disallowed-for-bad-bot/'))
            ->withHeader('User-Agent', 'BadBot');

        await($downloader->download($request), $this->eventLoop, 10);
    }
}