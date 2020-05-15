<?php

namespace Schrapert\Feature;

use Schrapert\Core\Event\SpiderOpened;
use Schrapert\Downloading\Event\DownloadRequestEvent;
use Schrapert\Downloading\Event\ResponseDownloadedEvent;
use Schrapert\Events\EventDispatcherInterface;
use Schrapert\Log\LoggerInterface;

/**
 * Feature which throttles the downloads.
 *
 * @package Schrapert\Feature
 */
class AutoThrottleFeature implements FeatureInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $events;
    /**
     * @var float
     */
    private $targetConcurrency;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var float
     */
    private $minDelay;
    /**
     * @var float
     */
    private $maxDelay;
    /**
     * @var float
     */
    private $startDelay;

    private $fingerprinter;

    public function __construct(EventDispatcherInterface $events, LoggerInterface $logger, $targetConcurrency = 1.0, $minDelay = 1.0, $maxDelay = 60.0, $startDelay = 5.0)
    {
        $this->events = $events;
        $this->targetConcurrency = $targetConcurrency;
        $this->logger = $logger;
        $this->minDelay = $minDelay;
        $this->maxDelay = $maxDelay;
        $this->startDelay = $startDelay;
    }

    public function init()
    {
        $this->events->addListener('spider-opened', [$this, 'onSpiderOpened']);
        $this->events->addListener('response-downloaded', [$this, 'onResponseDownloaded']);
        $this->events->addListener('download-request', [$this, 'onDownloadRequest']);
    }

    public function withTargetConcurrency($concurrency)
    {
        $new = clone $this;
        $new->targetConcurrency = $concurrency;
        return $new;
    }

    public function withMinDelay($delay)
    {
        $new = clone $this;
        $new->minDelay = $delay;
        return $new;
    }

    public function withMaxDelay($delay)
    {
        $new = clone $this;
        $new->maxDelay = $delay;
        return $new;
    }

    public function withStartDelay($delay)
    {
        $new = clone $this;
        $new->startDelay = $delay;
        return $new;
    }

    public function onSpiderOpened(SpiderOpened $event)
    {
        $spider = $event->getSpider();
        if ($spider instanceof SpiderProvidingDownloadDelayInterface) {
            $this->minDelay = $spider->getDownloadDelay();
        }
        if (null === $this->startDelay) {
            $this->startDelay = $this->minDelay;
        }
    }

    public function onDownloadRequest(DownloadRequestEvent $event)
    {
    }

    public function onResponseDownloaded(ResponseDownloadedEvent $event)
    {
    }
}
