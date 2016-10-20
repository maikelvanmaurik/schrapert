<?php
namespace Schrapert\Crawl;

interface RequestInterface
{
    /**
     * @param $key
     * @param $value
     * @return static
     */
    public function withMetaData($key, $value);

    public function getMetaData($key = null, $default = null);

    public function getCallback();
    /**
     * @param callable $callback
     * @return static
     */
    public function withCallback(callable $callback);
}