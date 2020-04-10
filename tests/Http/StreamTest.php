<?php

namespace Schrapert\Test\Http;

use Schrapert\Http\Stream;
use Schrapert\Tests\TestCase;

class StreamTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructorThrowsExceptionOnInvalidArgument()
    {
        new Stream(true);
    }

    public function testConvertsToString()
    {
        $fp = fopen('php://temp', 'w+');
        fwrite($fp, 'test');
        $stream = new Stream($fp);
        $this->assertEquals('test', (string) $stream);
        $this->assertEquals('test', (string) $stream);
        $stream->close();
    }
}
