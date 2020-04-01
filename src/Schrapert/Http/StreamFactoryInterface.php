<?php
namespace Schrapert\Http;

use Psr\Http\Message\StreamInterface;

interface StreamFactoryInterface
{
    /**
     * @param string|resource|StreamInterface|null $body
     * @return StreamInterface
     */
    public function createStream($body = null);
}