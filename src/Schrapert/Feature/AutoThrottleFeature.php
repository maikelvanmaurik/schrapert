<?php
namespace Schrapert\Feature;

use Schrapert\Core\Event\SpiderOpenedEvent;
use Schrapert\Event\EventDispatcherInterface;
use Schrapert\Http\Downloader\Event\DownloadRequestEvent;
use Schrapert\Http\Downloader\Event\ResponseDownloadedEvent;
use Schrapert\Log\LoggerInterface;

class AutoThrottleFeature implements FeatureInterface
{
    private $events;

    private $targetConcurrency;

    private $logger;

    private $minDelay;

    private $maxDelay;

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
        $this->events->addListener('spider-opened', array($this, 'onSpiderOpened'));
        $this->events->addListener('response-downloaded', array($this, 'onResponseDownloaded'));
        $this->events->addListener('download-request', array($this, 'onDownloadRequest'));
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

    public function onSpiderOpened(SpiderOpenedEvent $event)
    {
        $spider = $event->getSpider();
        if($spider instanceof SpiderProvidingDownloadDelayInterface) {
            $this->minDelay = $spider->getDownloadDelay();
        }
        if(null === $this->startDelay) {
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