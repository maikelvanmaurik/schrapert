<?php

namespace Schrapert\Http;

use GuzzleHttp\Psr7\Response as GuzzleResponse;

use Psr\Http\Message\StreamInterface;

class Response extends GuzzleResponse implements ResponseInterface
{
    public function withMetaData($key, $value)
    {
        // TODO: Implement withMetaData() method.
    }

    public function getMetaData($key = null, $default = null)
    {
        // TODO: Implement getMetaData() method.
    }
}
