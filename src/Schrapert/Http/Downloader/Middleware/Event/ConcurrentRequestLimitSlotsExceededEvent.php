<?php
namespace Schrapert\Http\Downloader\Middleware\Event;

use Schrapert\Event\Event;
use Schrapert\Http\RequestInterface;

class ConcurrentRequestLimitSlotsExceededEvent extends Event
{
    private $request;

    private $delay;

    public function __construct(RequestInterface $request, $delay)
    {
        parent::__construct('concurrent-request-limit-slots-exceeded');
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