<?php
namespace Schrapert\Test\Unit\Http;

use Schrapert\Http\Cache\RFC2616Policy;
use Schrapert\Http\Request;
use Schrapert\Http\Response;
use Schrapert\Test\Unit\TestCase;
use DateTime;

class Rfc2616PolicyTest extends TestCase
{
    const SECONDS_IN_DAY = 86400;
    /**
     * @var RFC2616Policy
     */
    private $policy;

    public function setUp()
    {
        $this->policy = new RFC2616Policy();
        parent::setUp();
    }

    public function testCanAddIgnoreSchemes()
    {
        $policy = $this->policy->withoutIgnoreSchemes($this->policy->getIgnoreSchemes());
        $this->assertCount(0, $policy->getIgnoreSchemes());

        $policy = $policy->withIgnoreSchemes(['file']);
        $this->assertCount(1, $policy->getIgnoreSchemes());


    }

    public function testRequestShouldNotBeCachedWhenItContainsACacheControlNoStoreHeader()
    {
        $request = new Request('http://foo.bar', 'GET', ['Cache-Control' => 'no-store']);
        $this->assertFalse($this->policy->shouldCacheRequest($request));
    }

    public function testRequestsShouldNotBeCachedWhenTheRequestSchemeIsIgnored()
    {
        $policy = $this->policy
            ->withIgnoreSchemes(['file'])
            ->withoutIgnoreSchemes(['http']);

        $this->assertFalse($policy->shouldCacheRequest(new Request('file://test.html')));
        $this->assertTrue($policy->shouldCacheRequest(new Request('http://bla.com')));
    }

    /**
     * Max-stale if used when the client is willing to except a cached response
     * which is expired by no more than the given seconds
     *
     * @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.9.3
     */
    public function testCachedResponsesAreWorkingWithMaxStaleCacheControlHeaders()
    {
        $now = mktime(0, 0, 0, 1, 1, 2000);
        $cachedResponseTime = $now - 3600; // Response was received 1 hour ago
        $policy = $this->policy->withCurrentTime($now);
        $cachedResponse = (new Response(200, null, [
            'Expires' => date_create()->setTimestamp($now - 1800)->format(DateTime::RFC1123),
            'Date' => date_create()->setTimestamp($cachedResponseTime)->format(DateTime::RFC1123)
        ]));
        $request = (new Request('http://foo.bar', 'GET', ['Cache-Control' => 'max-stale=' . (self::SECONDS_IN_DAY * 2)]));
        $this->assertTrue($policy->isCachedResponseFresh($cachedResponse, $request));
        $request = (new Request('http://foo.bar', 'GET', ['Cache-Control' => 'max-stale=120']));
        $this->assertFalse($policy->isCachedResponseFresh($cachedResponse, $request));
    }

    /**
     * Indicates that the client is willing to accept a response whose freshness lifetime is no less than its current
     * age plus the specified time in seconds. That is, the client wants a response that will still be fresh for at
     * least the specified number of seconds.
     */
    public function testCachedResponseAreWorkingWithMinFreshHeader()
    {
        $now = mktime(0, 0, 0, 1, 1, 2000);
        $cachedResponseTime = $now - 3600; // Response was received 1 hour ago
        $policy = $this->policy->withCurrentTime($now);
        $cachedResponse = (new Response(200, null, [
            'Expires' => date_create()->setTimestamp($now + 1800)->format(DateTime::RFC1123),
            'Date' => date_create()->setTimestamp($cachedResponseTime)->format(DateTime::RFC1123)
        ]));
        $request = (new Request('http://foo.bar', 'GET', ['Cache-Control' => 'min-fresh=' . (self::SECONDS_IN_DAY * 2)]));
        $this->assertFalse($policy->isCachedResponseFresh($cachedResponse, $request));
        $request = (new Request('http://foo.bar', 'GET', ['Cache-Control' => 'min-fresh=120']));
        $this->assertTrue($policy->isCachedResponseFresh($cachedResponse, $request));
    }

    public function testCachedResponsesAreWorkingWithAMaxAge()
    {
        $now = mktime(0, 0, 0, 1, 1, 2000);
        $cachedResponseTime = $now - 3600; // Response was received 1 hour ago
        $policy = $this->policy->withCurrentTime($now);

        $request = (new Request('http://foo.bar', 'GET', ['Cache-Control' => 'max-age=3600']));

        $response = (new Response(200, null, [
            'Age' => '24'
        ]));
        $this->assertTrue($policy->isCachedResponseFresh($response, $request));

        $response = (new Response(200, null, [
            'Age' => '86400'
        ]));
        $this->assertFalse($policy->isCachedResponseFresh($response, $request));
    }
}