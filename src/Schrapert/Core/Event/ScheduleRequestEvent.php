<?php
namespace Schrapert\Core\Event;

use Schrapert\Crawl\RequestInterface;
use Schrapert\Event\Event;
use Schrapert\SpiderInterface;

class ScheduleRequestEvent extends Event
{
    private $request;

    private $spider;

    public function __construct(RequestInterface $request, SpiderInterface $spider)
    {
        parent::__construct('schedule-request');
        $this->request = $request;
        $this->spider = $spider;
    }

    public function getSpider()
    {
        return $this->spider;
    }

    public function getRequest()
    {
        return $this->request;
    }
}