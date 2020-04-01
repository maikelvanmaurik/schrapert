<?php
namespace Schrapert\Core\Event;

use Schrapert\Event\Event;
use Schrapert\SpiderInterface;

class SpiderClosedEvent extends Event
{
    private $spider;

    public function __construct(SpiderInterface $spider)
    {
        parent::__construct('spider-closed');
        $this->spider = $spider;
    }

    public function getSpider()
    {
        return $this->spider;
    }
}