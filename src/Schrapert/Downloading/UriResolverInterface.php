<?php

namespace Schrapert\Downloading;

use Psr\Http\Message\UriInterface as PsrUri;

interface UriResolverInterface
{
    /**
     * @param UriInterface $uri
     * @param $rel
     * @return UriInterface
     */
    public function resolve(PsrUri $uri, $rel);
}
