<?php
namespace Schrapert\Http;

use Psr\Http\Message\UriInterface;

interface UriResolverInterface
{
    /**
     * @param UriInterface $uri
     * @param $rel
     * @return UriInterface
     */
    public function resolve(UriInterface $uri, $rel);
}