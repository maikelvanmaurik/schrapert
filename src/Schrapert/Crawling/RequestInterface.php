<?php
namespace Schrapert\Crawl;

interface RequestInterface extends MessageInterface
{
    /**
     * @return callable
     */
    public function getCallback();
    /**
     * @param callable $callback
     * @return static
     */
    public function withCallback(callable $callback);
}