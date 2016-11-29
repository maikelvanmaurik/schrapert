<?php
namespace Schrapert\Test\Integration\Http;

use Clue\React\Block;
use Schrapert\Http\ResponseInterface;
use Schrapert\Test\Integration\TestCase;
use Schrapert\Runner;
use Schrapert\Feature\AutoThrottleFeature;
use Schrapert\Test\Integration\TestSpider;
use Schrapert\Feed\ExporterFactory;

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

    public function setUp()
    {
        $this->runner = $this->getContainer()->get('crawler_runner');
        $this->eventLoop = $this->getContainer()->get('event_loop');
        $this->autoThrottleFeature = $this->getContainer()->get('auto_throttle_feature');
        return parent::setUp();
    }

    public function testAutoThrottle()
    {
        return;
        $feature = $this->autoThrottleFeature;
        $this->runner
            ->withFeature($feature)
            ->withSpider(new TestSpider(['http://throttle.schrapert.dev/delay.php'], function(ResponseInterface $response) {
                $html = (string)$response->getBody();
                $products = $this->parseProducts($html);
                return $products;
            }))
            ->start();
    }
}