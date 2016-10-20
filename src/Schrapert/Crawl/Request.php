<?php
namespace Schrapert\Crawl;

class Request implements RequestInterface
{
    private $callback;

    private $meta = [];

    public function __construct(callable $callback = null)
    {
        $this->setCallback($callback);
    }

    public function getCallback()
    {
        return $this->callback;
    }

    public function setCallback(callable $callback)
    {
        $this->callback = $callback;
    }

    public function setMetaData($key, $value)
    {
        // TODO: Implement setMetaData() method.
    }

    public function getMetaData($key = null, $default = null)
    {
        if(null === $key) {
            return $this->meta;
        }

        return array_key_exists($key, $this->meta) ? $this->meta[$key] : $default;
    }
}