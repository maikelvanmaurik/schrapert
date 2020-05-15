<?php

namespace Schrapert\Downloading\Middleware\Events;

use Schrapert\Events\Event;
use Schrapert\Http\RequestInterface;

/**
 * An event which gets dispatched when the concurrent request downloader
 * middleware runs out of free slots.
 *
 * @package Schrapert\Http\Crawling\Middleware\Events
 */
class ConcurrentRequestLimitSlotsExceededEvent extends Event
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
