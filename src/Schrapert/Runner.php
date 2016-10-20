<?php
namespace Schrapert;

use Schrapert\Core\ExecutionEngine;
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

    public function __construct(LoopInterface $loop, SignalManager $signals, ExecutionEngine $engine)
    {
        $this->spiders = array();
        $this->engine = $engine;
        $this->signals = $signals;
        $this->loop = $loop;
    }

    public function addSpider(SpiderInterface $spider)
    {
        $this->spiders[] = $spider;
    }

    private function crawl(SpiderInterface $spider)
    {
        $this->engine->openSpider($spider, $spider->startRequests());
        return $this->engine->start();
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