<?php
namespace Schrapert\Crawl;

abstract class Message implements MessageInterface
{
    private $meta = [];

    public function __construct()
    {

    }

    /**
     * @param $key
     * @param $value
     * @return static
     */
    public function withMetadata($key, $value)
    {
        $new = clone $this;
        $new->meta[$key] = $value;
        return $new;
    }

    public function getMetadata($key = null, $default = null)
    {
        if(null === $key) {
            return $this->meta;
        }

        return array_key_exists($key, $this->meta) ? $this->meta[$key] : $default;
    }

}