<?php

namespace Schrapert\Scheduling\Events;

use Schrapert\Downloading\RequestInterface;

class ScheduleRequest
{
    public function __construct(RequestInterface $request)
    {
    }
}
