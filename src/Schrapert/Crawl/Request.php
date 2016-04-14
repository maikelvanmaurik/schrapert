<?php
namespace Schrapert\Crawl;

class Request implements RequestInterface
{
    private $uri;

    private $callback;

    public function __construct($uri = null, $method = 'GET', callable $callback = null)
    {
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function getCallback()
    {
        return $this->callback;
    }

    public function setCallback(callable $callback)
    {

    }

    public function setMetaData($key, $value)
    {
        // TODO: Implement setMetaData() method.
    }

    public function getMetaData($key, $default = null)
    {
        // TODO: Implement getMetaData() method.
    }


}