<?php

namespace Schrapert\Downloading\Events;

use DateTime;
use Schrapert\Events\Event;
use Schrapert\Downloading\RequestInterface;

class DownloadRequestEvent extends Event
{
    private $time;

    private $request;

    public function __construct(RequestInterface $request, DateTime $time)
    {
        $this->request = $request;
        $this->time = $time;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getTime()
    {
        return $this->time;
    }
}
