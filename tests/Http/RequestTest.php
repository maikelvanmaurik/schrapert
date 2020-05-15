<?php
namespace Schrapert\Tests\Http;

use Schrapert\Tests\TestCase;

class RequestTest extends TestCase
{
    public function testIsImmutable()
    {
        $request = new \Schrapert\Http\Request('http://schrapert.dev/foo');
        $request2 = $request->withMethod('POST');
        $this->assertNotSame($request, $request2);
        $this->assertEquals($request->getRequestTarget(), '/foo');
        $this->assertEquals($request2->getRequestTarget(), '/foo');


        $request3 = $request->withRequestTarget('test');
        $this->assertNotSame($request, $request3);
    }

    public function testRequestTargetIsProperlySetFromUri()
    {
        $requestWithoutSlashInPath =  new \Schrapert\Http\Request('http://schrapert.dev?foo=bar');
        $requestWithSlashInPath = new \Schrapert\Http\Request('http://schrapert.dev/?foo=bar');

        $this->assertEquals('/?foo=bar', $requestWithoutSlashInPath->getRequestTarget());
        $this->assertEquals('/?foo=bar', $requestWithSlashInPath->getRequestTarget());

        $requestWithPath = new \Schrapert\Http\Request('http://schrapert.dev/foo/?bar=baz');

        $this->assertEquals('/foo/?bar=baz', $requestWithPath->getRequestTarget());
    }
}
