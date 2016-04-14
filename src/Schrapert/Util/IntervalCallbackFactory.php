<?php
namespace Schrapert\Util;

use React\EventLoop\LoopInterface;

class IntervalCallbackFactory
{
    private $loop;

    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    /**
     * @param callable $callback
     * @return IntervalCallback
     */
    public function factory(callable $callback)
    {
        return new IntervalCallback($this->loop, $callback);
    }
}