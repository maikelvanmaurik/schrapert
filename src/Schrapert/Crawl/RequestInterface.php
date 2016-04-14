<?php
namespace Schrapert\Crawl;

interface RequestInterface
{
    public function setMetaData($key, $value);

    public function getMetaData($key, $default = null);

    public function getUri();

    public function getCallback();

    public function setCallback(callable $callback);
}