<?php
namespace Schrapert\Core\Event;

use Schrapert\Crawl\RequestInterface;
use Schrapert\Event\Event;
use Schrapert\SpiderInterface;

class RequestDroppedEvent extends Event
{
    private $request;

    private $spider;

    public function __construct(RequestInterface $request, SpiderInterface $spider)
    {
        $this->spider = $spider;
        $this->request = $request;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getSpider()
    {
        return $this->spider;
    }
}