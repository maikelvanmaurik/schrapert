<?php
namespace Schrapert\Tests\Integration\Http;

use React\EventLoop\LoopInterface;
use Schrapert\Runner;
use Schrapert\Tests\TestCase;
use Zend\Dom\Document;

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

    public function setUp(): void
    {
        $this->runner = $this->getContainer()->get('crawler_runner');
        $this->eventLoop = $this->getContainer()->get('event_loop');
        parent::setUp();
    }
}