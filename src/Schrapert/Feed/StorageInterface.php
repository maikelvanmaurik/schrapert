<?php
namespace Schrapert\Feed;

use Schrapert\IO\StreamInterface;

interface StorageInterface
{
    /**
     * @return StreamInterface
     */
    public function open();

    public function close(StreamInterface $stream);
}