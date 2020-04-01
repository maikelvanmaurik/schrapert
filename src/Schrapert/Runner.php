<?php
namespace Schrapert;

use Schrapert\Core\ExecutionEngine;
use Schrapert\Event\EventDispatcherInterface;
use Schrapert\Feature\FeatureInterface;
use React\EventLoop\LoopInterface;

/**
 * The runner provides the functionality to start running given spiders.
 *
 * @package Schrapert
 */
class Runner
{
    private $engine;
    /**
     * @var SpiderInterface[]
     */
    private $spiders;
    /**
     * @var LoopInterface
     */
    private $loop;
    /**
     * @var EventDispatcherInterface
     */
    private $events;
    /**
     * @var FeatureInterface[]
     */
    private $features;

    public function __construct(LoopInterface $loop, EventDispatcherInterface $events, ExecutionEngine $engine)
    {
        $this->spiders = [];
        $this->features = [];
        $this->engine = $engine;
        $this->events = $events;
        $this->loop = $loop;
    }

    public function withSpider(SpiderInterface $spider)
    {
        $new = clone $this;
        $new->spiders[] = $spider;
        return $new;
    }

    /**
     * Gets the features which were assigned to the runner.
     *
     * @return FeatureInterface[]
     */
    public function getFeatures()
    {
        return $this->features;
    }

    /**
     * Starts the crawling process for a given spider.
     *
     * @param SpiderInterface $spider
     * @return \React\Promise\PromiseInterface
     */
    private function crawl(SpiderInterface $spider)
    {
        foreach($this->getFeatures() as $feature) {
            $feature->init();
        }
        $this->engine->openSpider($spider, $spider->startRequests());
        return $this->engine->start();
    }

    public function withFeature(FeatureInterface $feature)
    {
        $new = clone $this;
        $new->features[] = $feature;
        return $new;
    }

    public function start($stop=true)
    {
        $finished = 0;
        foreach($this->spiders as $spider) {
            $this->crawl($spider)->then(function() use (&$finished) {
                $finished++;
                if($finished == count($this->spiders)) {
                    if($this->loop) {
                        $this->loop->stop();
                    }
                }
            });
        }
        $this->loop->run(); // Blocking call
    }

    public function stop()
    {

    }
}