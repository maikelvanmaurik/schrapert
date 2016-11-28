<?php
namespace Schrapert\IO;

interface WritableStreamInterface extends StreamInterface
{
    public function isWritable();

    public function write($data);

    public function end($data = null);
}