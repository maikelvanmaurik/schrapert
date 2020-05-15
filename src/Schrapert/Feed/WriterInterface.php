<?php

namespace Schrapert\Feed;

use Schrapert\IO\StreamInterface;
use Schrapert\Scraping\ItemInterface;

interface WriterInterface
{
    public function beginWriting(StreamInterface $stream);

    public function write(StreamInterface $stream, ItemInterface $item);

    public function endWriting(StreamInterface $stream);
}
