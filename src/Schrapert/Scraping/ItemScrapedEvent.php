<?php

namespace Schrapert\Scraping;

use Schrapert\Event\Event;
use Schrapert\SpiderInterface;

class ItemScrapedEvent extends Event
{
    private $spider;

    private $item;

    public function __construct(SpiderInterface $spider, ItemInterface $item)
    {
        parent::__construct('item-scraped');
        $this->item = $item;
        $this->spider = $spider;
    }

    public function getItem()
    {
        return $this->item;
    }

    public function getSpider()
    {
        return $this->spider;
    }
}
