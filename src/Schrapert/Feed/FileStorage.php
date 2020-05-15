<?php

namespace Schrapert\Feed;

use Schrapert\IO\Stream;
use Schrapert\IO\StreamInterface;

class FileStorage implements StorageInterface
{
    private $filename;

    private $stream;

    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    public function getFilename()
    {
        return $this->filename;
    }

    public function withFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     * @return mixed
     */
    public function open()
    {
        $resource = fopen($this->getFilename(), 'w+');
        $this->stream = new Stream($resource);
        return $this->stream;
    }

    public function close(StreamInterface $stream)
    {
        $stream->close();
    }
}
