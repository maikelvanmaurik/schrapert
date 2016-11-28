<?php
namespace Schrapert\Test\Integration\Http;

use Clue\React\Block;
use Schrapert\Test\Integration\TestCase;
use Schrapert\Runner;
use Zend\Dom\Document;
use React\EventLoop\LoopInterface;

class ItemPipelineTest extends TestCase
{
    /**
     * @var Runner
     */
    private $runner;
    /**
     * @var LoopInterface
     */
    private $eventLoop;

    public function setUp()
    {
        $this->runner = $this->getContainer()->get('crawler_runner');
        $this->eventLoop = $this->getContainer()->get('event_loop');
        parent::setUp();
    }
}