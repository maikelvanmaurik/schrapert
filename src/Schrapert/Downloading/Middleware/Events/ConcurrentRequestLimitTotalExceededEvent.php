<?php

namespace Schrapert\Downloading\Middleware\Events;

use Schrapert\Events\Event;
use Schrapert\Http\RequestInterface;

class ConcurrentRequestLimitTotalExceededEvent extends Event
{
    private $request;

    private $delay;

    public function __construct(RequestInterface $request, $delay)
    {
        $this->request = $request;
        $this->delay = $delay;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getDelay()
    {
        return $this->delay;
    }
}
