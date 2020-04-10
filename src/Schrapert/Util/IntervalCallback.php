<?php

namespace Schrapert\Util;

use React\EventLoop\LoopInterface;

class IntervalCallback
{
    private $loop;

    private $callback;

    private $timer;

    public function __construct(LoopInterface $loop, callable $callback)
    {
        $this->loop = $loop;
        $this->callback = $callback;
        $this->timer = null;
    }

    public function stop()
    {
        if ($this->timer) {
            $this->loop->cancelTimer($this->timer);
        }
    }

    public function start($interval)
    {
        if ($this->timer) {
            return;
        }
        $this->timer = $this->loop->addPeriodicTimer($interval, function () {
            call_user_func($this->callback);
        });
    }
}
