<?php
namespace Schrapert\Test\Integration\Http\Downloader;

use Schrapert\Crawl\Exception\IgnoreRequestException;
use Schrapert\Http\Request;
use Schrapert\Test\Integration\TestCase;
use Schrapert\Http\Downloader\Downloader;

class RobotsMiddlewareTest extends TestCase
{
    private $eventLoop;
    /**
     * @var Downloader
     */
    private $downloader;

    public function setUp()
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