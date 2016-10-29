<?php
namespace Schrapert\Crawl;

interface MessageInterface
{
    /**
     * @param $key
     * @param $value
     * @return static
     */
    public function withMetaData($key, $value);

    public function getMetaData($key = null, $default = null);
}