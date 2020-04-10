<?php

namespace Schrapert\Crawl;

interface MessageInterface
{
    /**
     * @param $key
     * @param $value
     * @return static
     */
    public function withMetadata($key, $value);

    public function getMetadata($key = null, $default = null);
}
