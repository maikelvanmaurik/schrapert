<?php
namespace Schrapert\Tests\Integration\Http\Downloader;

use Schrapert\Http\Cookies\CookieJarInterface;
use Schrapert\Http\Cookies\SetCookie;
use Schrapert\Http\Downloader\Downloader;
use Schrapert\Http\Request;
use Schrapert\Tests\TestCase;

class CookiesMiddlewareTest extends TestCase
{
    private $eventLoop;
    /**
     * @var Downloader
     */
    private $downloader;
    /**
     * @var CookieJarInterface
     */
    private $cookies;

    public function setUp(): void
    {
        $this->eventLoop = $this->getContainer()->get('event_loop');
        $this->downloader = $this->getContainer()->get('downloader');
        $this->cookies = $this->getContainer()->get('http_cookie_jar');
        parent::setUp();
    }

    public function testCookiesAreSet()
    {
        return;

        $cookieMiddleware = $this->getContainer()->get('downloader_middleware_cookies');

        $downloader = $this->downloader->withMiddleware($cookieMiddleware);

        $cookies = $this->cookies;

        // Clear the cookies so that we start with none
        $cookies->clear();

        $request = new Request('http://cookies.schrapert.dev');

        await($downloader->download($request), $this->eventLoop, 10);

        // When done we should have a PHPSESSID cookie
        $this->assertCount(1, array_filter(iterator_to_array($cookies->getIterator()), function(SetCookie $cookie) {
            return
                $cookie->getDomain() == 'cookies.schrapert.dev' &&
                $cookie->getName() == 'PHPSESSID';
        }));

        $this->assertEquals(1, array_reduce(iterator_to_array($cookies->getIterator()), function(&$carry, SetCookie $cookie) {
            if($cookie->getDomain() == 'cookies.schrapert.dev' && $cookie->getName() == 'times_visited') {
                $carry = intval($cookie->getValue());
            }
            return $carry;
        }));

        // Do another request so that the cookie jar gets used again and check if the times_visited got incremented by 1
        await($downloader->download($request), $this->eventLoop, 10);

        $this->assertEquals(2, array_reduce(iterator_to_array($cookies->getIterator()), function(&$carry, SetCookie $cookie) {
            if($cookie->getDomain() == 'cookies.schrapert.dev' && $cookie->getName() == 'times_visited') {
                $carry = intval($cookie->getValue());
            }
            return $carry;
        }));
    }
}