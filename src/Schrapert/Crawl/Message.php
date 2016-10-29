<?php
namespace Schrapert\Crawl;

class Message implements MessageInterface
{
    private $meta = [];

    public function __construct()
    {

    }

    public function withMetaData($key, $value)
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