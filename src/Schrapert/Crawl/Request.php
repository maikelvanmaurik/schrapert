<?php
namespace Schrapert\Crawl;

class Request extends Message implements RequestInterface
{
    private $callback;

    public function __construct(callable $callback = null)
    {
        $this->callback = $callback;
    }

    public function getCallback()
    {
        return $this->callback;
    }

    public function withCallback(callable $callback)
    {
        $new = clone $this;
        $new->callback = $callback;
        return $new;
    }
}