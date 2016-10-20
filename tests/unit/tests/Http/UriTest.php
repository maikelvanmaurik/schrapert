<?php
namespace Schrapert\Test\Unit;

use Schrapert\Http\Uri;

class UriTest extends \Schrapert\Test\Unit\TestCase
{
    public function testDoesParseProvidedUriProperly()
    {
        $str = 'https://user:pass@example.com:1337/some/test/path?q=abc#fragment';
        $uri = new Uri($str);

        $this->assertEquals('https', $uri->getScheme());
        $this->assertEquals('user:pass@example.com:1337', $uri->getAuthority());
        $this->assertEquals('user:pass', $uri->getUserInfo());
        $this->assertEquals('example.com', $uri->getHost());
        $this->assertEquals('/some/test/path', $uri->getPath());
        $this->assertEquals('q=abc', $uri->getQuery());
        $this->assertEquals('fragment', $uri->getFragment());
        $this->assertEquals('https://user:pass@example.com:1337/some/test/path?q=abc#fragment', (string)$uri);
    }

    public function testCanAssembleUriAndRetrievePartsProperly()
    {
        $uri = (new Uri())
            ->withScheme('https')
            ->withUserInfo('user', 'pass')
            ->withHost('example.com')
            ->withPort(1337)
            ->withPath('/some/test/path')
            ->withQuery('q=abc')
            ->withFragment('fragment');

        $this->assertEquals('https', $uri->getScheme());
        $this->assertEquals('user:pass@example.com:1337', $uri->getAuthority());
        $this->assertEquals('user:pass', $uri->getUserInfo());
        $this->assertEquals('example.com', $uri->getHost());
        $this->assertEquals('/some/test/path', $uri->getPath());
        $this->assertEquals('q=abc', $uri->getQuery());
        $this->assertEquals('fragment', $uri->getFragment());
        $this->assertEquals('https://user:pass@example.com:1337/some/test/path?q=abc#fragment', (string)$uri);
    }
}