<?php

namespace Schrapert\Core\Event;

use Schrapert\Events\Event;
use Schrapert\SpiderInterface;

class SpiderClosed extends Event
{
    private $spider;

    public function __construct(SpiderInterface $spider)
    {
        $this->spider = $spider;
    }

    public function getSpider()
    {
        return $this->spider;
    }
}
