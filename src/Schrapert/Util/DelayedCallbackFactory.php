<?php
namespace Schrapert\Util;

use React\EventLoop\LoopInterface;

class DelayedCallbackFactory
{
    private $loop;

    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    public function factory(callable $callback, array $args = [])
    {
        return new DelayedCallback($this->loop, $callback, $args);
    }
}