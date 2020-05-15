<?php

namespace Schrapert\IO;

interface StreamInterface
{
    public function write($data);

    public function isReadable();

    public function isWritable();

    public function read();

    public function seek($position = 0);

    public function close();
}
