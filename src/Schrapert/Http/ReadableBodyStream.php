<?php

namespace Schrapert\Http;

use Evenement\EventEmitter;
use Psr\Http\Message\StreamInterface;
use React\Stream\ReadableStreamInterface;
use React\Stream\Util;
use React\Stream\WritableStreamInterface;

/**
 * @internal
 */
class ReadableBodyStream extends EventEmitter implements ReadableStreamInterface, StreamInterface
{
    public function __construct(ReadableStreamInterface $input)
    {
        $this->input = $input;

        $that = $this;

        $input->removeAllListeners('data');

        $input->on('data', function ($data) use ($that) {
            if ($data instanceof StreamInterface) {
                $stream = $data;
                $data = $stream->getContents();
            }

            $that->emit('data', [$data, $that]);
        });
        $input->on('error', function ($error) use ($that) {
            $that->emit('error', [$error, $that]);
        });
        $input->on('end', function () use ($that) {
            $that->emit('end', [$that]);
            $that->emit('close', [$that]);
        });
        $input->on('close', function () use ($that) {
            $that->emit('close', [$that]);
        });
    }

    public function close()
    {
        $this->input->close();
    }

    public function isReadable()
    {
        return $this->input->isReadable();
    }

    public function pause()
    {
        $this->input->pause();
    }

    public function resume()
    {
        $this->input->resume();
    }

    public function pipe(WritableStreamInterface $dest, array $options = [])
    {
        Util::pipe($this, $dest, $options);

        return $dest;
    }

    public function eof()
    {
        return ! $this->isReadable();
    }

    public function __toString()
    {
        return '';
    }

    public function detach()
    {
        throw new \BadMethodCallException();
    }

    public function getSize()
    {
        return null;
    }

    public function tell()
    {
        throw new \BadMethodCallException();
    }

    public function isSeekable()
    {
        return false;
    }

    public function seek($offset, $whence = SEEK_SET)
    {
        throw new \BadMethodCallException();
    }

    public function rewind()
    {
        throw new \BadMethodCallException();
    }

    public function isWritable()
    {
        return false;
    }

    public function write($string)
    {
        throw new \BadMethodCallException();
    }

    public function read($length)
    {
        throw new \BadMethodCallException();
    }

    public function getContents()
    {
        throw new \BadMethodCallException();
    }

    public function getMetadata($key = null)
    {
        return ($key === null) ? [] : null;
    }
}
