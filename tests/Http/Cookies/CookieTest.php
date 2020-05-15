<?php

namespace Schrapert\Tests\Http\Cookies;

use Schrapert\Http\Cookies\Cookie;
use Schrapert\Tests\TestCase;

class CookieTest extends TestCase
{
    /**
     * @test
     */
    public function cookies_can_be_created_from_an_array()
    {
        $cookie = Cookie::fromArray([
            'name' => 'foo',
            'domain' => 'test.com',
            'path' => '/path',
            'value' => 'baz'
        ]);

        $this->assertEquals('foo', $cookie->getName());
        $this->assertEquals('test.com', $cookie->getDomain());
        $this->assertEquals('/path', $cookie->getPath());
        $this->assertEquals('baz', $cookie->getValue());
    }
}