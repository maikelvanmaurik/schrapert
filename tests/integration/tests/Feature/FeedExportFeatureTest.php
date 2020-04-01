<?php
namespace Schrapert\Test\Integration\Http;

use Clue\React\Block;
use Schrapert\Http\ResponseInterface;
use Schrapert\Test\Integration\ProductItem;
use Schrapert\Test\Integration\TestCase;
use Schrapert\Runner;
use Schrapert\Feature\FeedExportFeature;
use Schrapert\Test\Integration\TestSpider;
use Schrapert\Feed\ExporterFactory;
use DOMDocument;
use QueryPath;

class FeedExportFeatureTest extends TestCase
{
    /**
     * @var Runner
     */
    private $runner;

    private $eventLoop;
    /**
     * @var FeedExportFeature
     */
    private $feedExportFeature;
    /**
     * @var ExporterFactory
     */
    private $exporterFactory;

    public function setUp()
    {
        $this->runner = $this->getContainer()->get('crawler_runner');
        $this->eventLoop = $this->getContainer()->get('event_loop');
        $this->feedExportFeature = $this->getContainer()->get('feed_export_feature');
        $this->exporterFactory = $this->getContainer()->get('feed_exporter_factory');
        return parent::setUp();
    }

    private function parseProducts($html)
    {
        return false;
        $products = [];
        $doc = new DOMDocument();
        $doc->loadHTML($html);
        foreach($this->css($doc, '.product') as $product) {
            $name = $this->css($doc, '.name', $product)->item(0)->textContent;
            $price = $this->css($doc, '.price', $product)->item(0)->textContent;
            var_dump($name, $price);
            $products[] = new ProductItem($name, $price);
        }
        return $products;
    }

    public function testDoesSupportMultipleExporters()
    {
        $exportPath = ETC_DIR . 'export-test/';
        if(!is_dir($exportPath)) {
            mkdir($exportPath, 0777, true);
        }

        $feature = $this->feedExportFeature
            ->withExporter($this->exporterFactory->createExporter('json', 'file://' . $exportPath . 'feed.json'))
            ->withExporter($this->exporterFactory->createExporter('csv', 'file://' . $exportPath . 'feed.csv'))
            ->withExporter($this->exporterFactory->createExporter('jsonlines', 'file://' . $exportPath . 'feed.jsonl'))
            ->withExporter($this->exporterFactory->createExporter('xml', 'file://' . $exportPath . 'feed.xml'));
        $this->runner
            ->withFeature($feature)
            ->withSpider(new TestSpider(['http://webshop.schrapert.dev/products.php'], function(ResponseInterface $response) {
                $html = (string)$response->getBody();
                $products = $this->parseProducts($html);
                return $products;
            }))
            ->start();
    }
}