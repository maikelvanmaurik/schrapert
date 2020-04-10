<?php

namespace Schrapert\Core\Event;

use Schrapert\Crawl\RequestInterface;
use Schrapert\Event\Event;
use Schrapert\SpiderInterface;

/**
 * Represents an event which is dispatched when a request is going to be scheduled into the scheduler.
 */
class ScheduleRequestEvent extends Event
{
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var SpiderInterface
     */
    private $spider;

    public function __construct(RequestInterface $request, SpiderInterface $spider)
    {
        parent::__construct('schedule-request');
        $this->request = $request;
        $this->spider = $spider;
    }

    /**
     * @return SpiderInterface
     */
    public function getSpider()
    {
        return $this->spider;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }
}
