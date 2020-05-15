<?php

namespace Schrapert\Downloading;

interface UriFactoryInterface
{
    /**
     * @param $scheme
     * @param $authority
     * @param $path
     * @param $query
     * @param $fragment
     * @return UriInterface
     */
    public function createFromComponents($scheme, $authority, $path, $query, $fragment);

    public function createFromParts(array $parts);
}
