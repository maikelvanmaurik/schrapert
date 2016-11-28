<?php
namespace Schrapert;

use Schrapert\Core\ExecutionEngine;
use Schrapert\Event\EventDispatcherInterface;
use Schrapert\Feature\FeatureInterface;
use React\EventLoop\LoopInterface;

class Runner
{
    private $engine;
    /**
     * @var \Schrapert\SpiderInterface[]
     */
    private $spiders;

    private $loop;

    private $events;

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
     * @return FeatureInterface[]
     */
    public function getFeatures()
    {
        return $this->features;
    }

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