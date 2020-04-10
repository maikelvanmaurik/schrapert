<?php

namespace Schrapert\Core\Event;

use Schrapert\Event\Event;
use Schrapert\SpiderInterface;

class SpiderOpenedEvent extends Event
{
    private $spider;

    public function __construct(SpiderInterface $spider)
    {
        parent::__construct('spider-opened');
        $this->spider = $spider;
    }

    public function getSpider()
    {
        return $this->spider;
    }
}
