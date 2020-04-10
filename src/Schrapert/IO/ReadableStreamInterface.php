<?php

namespace Schrapert\IO;

interface ReadableStreamInterface extends StreamInterface
{
    public function isReadable();

    public function read();
}
