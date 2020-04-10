<?php

namespace Schrapert\IO;

class Stream implements WritableStreamInterface, ReadableStreamInterface
{
    private $resource;

    public function __construct($resource)
    {
        $this->resource = $resource;
    }

    public function isReadable()
    {
    }

    public function seek($position = 0)
    {
        // TODO: Implement seek() method.
    }

    public function close()
    {
        // TODO: Implement close() method.
    }

    public function read()
    {
        // TODO: Implement read() method.
    }

    public function isWritable()
    {
        // TODO: Implement isWritable() method.
    }

    public function write($data)
    {
        fwrite($this->resource, $data);
    }

    public function end($data = null)
    {
        // TODO: Implement end() method.
    }
}
