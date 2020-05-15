<?php

namespace Schrapert\Tests\Http\Cookies;

use Schrapert\Http\Cookies\Cookie;
use Schrapert\Http\Cookies\CookieJar;
use Schrapert\Http\Request;
use Schrapert\Http\Response;
use Schrapert\Tests\TestCase;

class CookieJarTest extends TestCase
{
    /**
     * @test
     */
    public function cookies_can_be_cleared_by_domain()
    {
        $jar = new CookieJar([
            Cookie::fromArray([
                'domain' => 'test.com',
                'name' => 'foo',
                'value' => 'bar'
            ]),
            Cookie::fromArray([
                'domain' => 'foo.com',
                'name' => 'foo',
                'value' => 'baz'
            ]),
            Cookie::fromArray([
                'domain' => 'foo.com',
                'name' => 'foo2',
                'value' => 'baz2'
            ])
        ]);

        $this->assertCount(3, $jar);

        $jar->clear('test.com');

        $this->assertCount(2, $jar);

        foreach ($jar as $cookie) {
            $this->assertEquals('foo.com', $cookie->getDomain());
        }

        $jar->clear('foo.com');

        $this->assertCount(0, $jar);
    }

    /**
     * @test
     */
    public function cookies_can_be_cleared_by_path()
    {
        $jar = new CookieJar([
            Cookie::fromArray([
                'domain' => 'test.com',
                'name' => 'foo',
                'path' => '/',
                'value' => 'bar'
            ]),
            Cookie::fromArray([
                'domain' => 'foo.com',
                'name' => 'foo',
                'path' => '/foo',
                'value' => 'baz'
            ]),
            Cookie::fromArray([
                'domain' => 'foo.com',
                'name' => 'foo2',
                'path' => '/',
                'value' => 'baz2'
            ])
        ]);

        $this->assertCount(3, $jar);

        $jar->clear(null, '/');

        $this->assertCount(1, $jar);

        foreach ($jar as $cookie) {
            $this->assertEquals('foo.com', $cookie->getDomain());
        }

        $jar->clear(null, '/foo');

        $this->assertCount(0, $jar);
    }

    /**
     * @test
     */
    public function cookies_can_be_cleared_by_name()
    {
        $jar = new CookieJar([
            Cookie::fromArray([
                'domain' => 'test.com',
                'name' => 'foo',
                'path' => '/',
                'value' => 'bar'
            ]),
            Cookie::fromArray([
                'domain' => 'foo.com',
                'name' => 'foo',
                'path' => '/foo',
                'value' => 'baz'
            ]),
            Cookie::fromArray([
                'domain' => 'foo.com',
                'name' => 'foo2',
                'path' => '/',
                'value' => 'baz2'
            ])
        ]);

        $this->assertCount(3, $jar);

        $jar->clear(null, null, 'foo');

        $this->assertCount(1, $jar);

        foreach ($jar as $cookie) {
            $this->assertEquals('foo.com', $cookie->getDomain());
        }

        $jar->clear(null, null, 'foo2');

        $this->assertCount(0, $jar);
    }

    /**
     * @test
     */
    public function sessionCookiesCanBeCleared()
    {
        $jar = new CookieJar([
            Cookie::fromArray([
                'domain' => 'test.com',
                'name' => 'foo',
                'path' => '/',
                'value' => 'bar',
                'discard' => true
            ]),
            Cookie::fromArray([
                'domain' => 'foo.com',
                'name' => 'foo',
                'path' => '/foo',
                'value' => 'baz',
                'discard' => true
            ]),
            Cookie::fromArray([
                'domain' => 'foo.com',
                'name' => 'foo2',
                'path' => '/',
                'value' => 'baz2'
            ])
        ]);

        $this->assertCount(3, $jar);

        $jar->clearSessionCookies();

        $this->assertCount(1, $jar);
    }

    /**
     * @test
     */
    public function cookiesCanBeExtractedFromTheResponseHeaders()
    {
        $jar = new CookieJar();

        $request = new Request('GET', 'http://foo.com');
        $response = new Response(
            200,
            [
                'Set-Cookie' => 'foo=bar; Secure; HttpOnly; Path=/path'
            ],
            ''
        );

        $jar->extractCookies($response, $request);

        $this->assertCount(1, $jar);

        foreach ($jar as $cookie) {
            $this->assertEquals('foo.com', $cookie->getDomain());
        }
    }
}
