<?php
namespace Schrapert;

use Schrapert\Core\ExecutionEngine;
use Schrapert\Core\ExecutionEngineFactory;
use Schrapert\Signal\SignalManager;
use React\EventLoop\LoopInterface;

class Runner
{
    private $signals;

    private $engine;
    /**
     * @var \Schrapert\SpiderInterface[]
     */
    private $spiders;

    private $loop;

    private $engineFactory;

    public function __construct(LoopInterface $loop, SignalManager $signals, ExecutionEngineFactory $engineFactory)
    {
        $this->spiders = array();
        $this->engineFactory = $engineFactory;
        $this->signals = $signals;
        $this->loop = $loop;
    }

    public function addSpider(SpiderInterface $spider)
    {
        $this->spiders[] = $spider;
    }

    private function crawl(SpiderInterface $spider)
    {
        $engine = $this->engineFactory->factory();
        $engine->openSpider($spider, $spider->startRequests());
        return $engine->start();
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