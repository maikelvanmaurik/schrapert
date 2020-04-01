<?php
namespace Schrapert\Test\Http;

use Schrapert\Http\Response;
use Schrapert\Tests\TestCase;

class ResponseTest extends TestCase
{
    public function testDefaultConstructor()
    {
        $r = new Response();
        $this->assertSame(200, $r->getStatusCode());
        $this->assertSame('1.1', $r->getProtocolVersion());
        $this->assertSame('OK', $r->getReasonPhrase());
        $this->assertSame([], $r->getHeaders());
        $this->assertNull($r->getBody());
        $this->assertSame('', (string)$r->getBody());
    }

    public function testCanConstructWithHeaders()
    {
        $r = new Response(200, null, ['Foo' => 'Bar']);
        $this->assertSame(['Foo' => ['Bar']], $r->getHeaders());
        $this->assertSame('Bar', $r->getHeaderLine('Foo'));
        $this->assertSame(['Bar'], $r->getHeader('Foo'));
    }
}