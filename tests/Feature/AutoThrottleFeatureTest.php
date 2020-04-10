<?php
namespace Schrapert\Tests\Integration\Http;

use Schrapert\Feature\AutoThrottleFeature;
use Schrapert\Feed\ExporterFactory;
use Schrapert\Http\ResponseInterface;
use Schrapert\Runner;
use Schrapert\Tests\TestCase;
use Schrapert\Tests\Integration\Fixtures\TestSpider;

class AutoThrottleFeatureTest extends TestCase
{
    /**
     * @var Runner
     */
    private $runner;

    private $eventLoop;
    /**
     * @var AutoThrottleFeature
     */
    private $autoThrottleFeature;
    /**
     * @var ExporterFactory
     */
    private $exporterFactory;

    public function setUp(): void
    {
        $this->runner = $this->getContainer()->get('crawler_runner');
        $this->eventLoop = $this->getContainer()->get('event_loop');
        $this->autoThrottleFeature = $this->getContainer()->get('auto_throttle_feature');
        parent::setUp();
    }

    public function testAutoThrottle()
    {
        $feature = $this->autoThrottleFeature;
        $this->runner
            ->withFeature($feature)
            ->withSpider(new TestSpider(['http://throttle.schrapert.dev/delay.php'], function(ResponseInterface $response) {
                var_dump("CALLBACK!");
            }))
            ->start();
    }
}