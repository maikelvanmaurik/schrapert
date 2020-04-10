<?php

namespace Schrapert\Feature;

use Schrapert\Core\Event\SpiderClosedEvent;
use Schrapert\Core\Event\SpiderOpenedEvent;
use Schrapert\Event\EventDispatcherInterface;
use Schrapert\Feed\ExporterInterface;
use Schrapert\Scraping\ItemScrapedEvent;

class FeedExportFeature implements FeatureInterface
{
    private $events;

    public function __construct(EventDispatcherInterface $events)
    {
        $this->exporters = [];
        $this->events = $events;
    }

    public function withExporter(ExporterInterface $exporter)
    {
        $new = clone $this;
        $new->exporters[] = $exporter;

        return $new;
    }

    /**
     * @return ExporterInterface[]
     */
    public function getExporters()
    {
        return $this->exporters;
    }

    public function init()
    {
        $this->events->addListener('spider-opened', [$this, 'openSpider']);
        $this->events->addListener('spider-closed', [$this, 'closeSpider']);
        $this->events->addListener('item-scraped', [$this, 'itemScraped']);
    }

    public function itemScraped(ItemScrapedEvent $event)
    {
        foreach ($this->getExporters() as $exporter) {
            $exporter->exportItem($event->getSpider(), $event->getItem());
        }
    }

    public function openSpider(SpiderOpenedEvent $event)
    {
        foreach ($this->getExporters() as $exporter) {
            $exporter->startExporting($event->getSpider());
        }
    }

    public function closeSpider(SpiderClosedEvent $event)
    {
        foreach ($this->getExporters() as $exporter) {
            $exporter->finishExporting($event->getSpider());
        }
    }
}
