<?php
namespace Schrapert\Util;

use React\EventLoop\LoopInterface;

class DelayedCallback
{
    private $loop;

    private $callback;

    private $args;

    private $timer;

    public function __construct(LoopInterface $loop, callable $callback, array $args = null)
    {
        $this->loop = $loop;
        $this->callback = $callback;
        $this->args = $args;
    }

    public function schedule($delay = 0)
    {
        $this->timer = $this->loop->addTimer($delay, function() {
            call_user_func_array($this->callback, $this->args);
        });
    }

    public function cancel()
    {
        if ($this->timer) {
            $this->loop->cancelTimer($this->timer);
        }
    }
}