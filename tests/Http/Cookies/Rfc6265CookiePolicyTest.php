<?php

namespace Schrapert\Tests\Http\Cookies;

use GuzzleHttp\Psr7\Request;
use Schrapert\Http\Cookies\Cookie;
use Schrapert\Http\Cookies\CookieJar;
use Schrapert\Http\Cookies\Rfc6265CookiePolicy;
use Schrapert\Tests\TestCase;

class Rfc6265CookiePolicyTest extends TestCase
{
    /**
     * @test
     */
    public function cookiesAreNotReturnedForRequestsWithAnIpAddressAsASuffix()
    {
        $policy = new Rfc6265CookiePolicy();
        $jar = new CookieJar([
            Cookie::fromArray([
                'name' => 'foo',
                'value' => 'bar',
                'domain' => '0.127.0.0.1'
            ]),
            Cookie::fromArray([
                'name' => 'foo2',
                'value' => 'bar2',
                'domain' => '1.127.0.0.1'
            ])
        ], $policy);
        $cookies = $policy->cookiesForRequest($jar, new Request('GET', 'http://127.0.0.1'));
        $this->assertCount(0, $cookies);
    }

    /**
     * @test
     */
    public function cookiesAreReturnedForRequestsWhereTheDomainsIsAnExactIpMatch()
    {
        $policy = new Rfc6265CookiePolicy();
        $jar = new CookieJar([
            Cookie::fromArray([
                'name' => 'foo',
                'value' => 'bar',
                'domain' => '127.0.0.1'
            ]),
            Cookie::fromArray([
                'name' => 'foo2',
                'value' => 'bar2',
                'domain' => '127.0.0.1'
            ])
        ], $policy);
        $cookies = $policy->cookiesForRequest($jar, new Request('GET', 'http://127.0.0.1'));
        $this->assertCount(2, $cookies);
    }

    /**
     * @test
     */
    public function cookiesAreReturnedToSubdomains()
    {
        $policy = new Rfc6265CookiePolicy();
        $jar = new CookieJar([
            Cookie::fromArray([
                'name' => 'foo',
                'value' => 'bar',
                'domain' => 'foo.com'
            ]),
            Cookie::fromArray([
                'name' => 'foo2',
                'value' => 'bar2',
                'domain' => 'foo.com'
            ]),
            Cookie::fromArray([
                'name' => 'foo2',
                'value' => 'bar2',
                'domain' => 'baz.com'
            ])
        ], $policy);
        $cookies = $policy->cookiesForRequest($jar, new Request('GET', 'http://test.foo.com'));
        $this->assertCount(2, $cookies);
    }

    /**
     * @test
     */
    public function cookiesAreReturnedToSubdomainsWithAnDotAsPrefix()
    {
        $policy = new Rfc6265CookiePolicy();
        $jar = new CookieJar([
            Cookie::fromArray([
                'name' => 'foo',
                'value' => 'bar',
                'domain' => '.foo.com'
            ]),
            Cookie::fromArray([
                'name' => 'foo2',
                'value' => 'bar2',
                'domain' => '.foo.com'
            ]),
            Cookie::fromArray([
                'name' => 'foo2',
                'value' => 'bar2',
                'domain' => 'baz.com'
            ])
        ], $policy);
        $cookies = $policy->cookiesForRequest($jar, new Request('GET', 'http://test.foo.com'));
        $this->assertCount(2, $cookies);
    }

    /**
     * @test
     */
     public function secureCookiesAreOnlyReturnedToSecureRequests() {
         $policy = new Rfc6265CookiePolicy();
         $jar = new CookieJar([
             Cookie::fromArray([
                 'name' => 'foo',
                 'value' => 'bar',
                 'domain' => 'foo.com',
                 'secure' => false
             ]),
             Cookie::fromArray([
                 'name' => 'foo2',
                 'value' => 'bar2',
                 'domain' => 'foo.com',
                 'secure' => true
             ]),
             Cookie::fromArray([
                 'name' => 'foo2',
                 'value' => 'bar2',
                 'domain' => 'baz.com',
                 'secure' => true
             ])
         ], $policy);
         $cookies = $policy->cookiesForRequest($jar, new Request('GET', 'http://test.foo.com'));
         $this->assertCount(1, $cookies);

         $cookies = $policy->cookiesForRequest($jar, new Request('GET', 'https://test.foo.com'));
         $this->assertCount(2, $cookies);
     }
}