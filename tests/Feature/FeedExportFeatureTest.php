<?php
namespace Schrapert\Tests\Integration\Http;

use DOMDocument;
use QueryPath;
use Schrapert\Feature\FeedExportFeature;
use Schrapert\Feed\ExporterFactory;
use Schrapert\Http\ResponseInterface;
use Schrapert\Runner;
use Schrapert\Test\Integration\ProductItem;
use Schrapert\Tests\TestCase;
use Schrapert\Test\Integration\TestSpider;

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

    public function setUp(): void
    {
        $this->runner = $this->getContainer()->get('crawler_runner');
        $this->eventLoop = $this->getContainer()->get('event_loop');
        $this->feedExportFeature = $this->getContainer()->get('feed_export_feature');
        $this->exporterFactory = $this->getContainer()->get('feed_exporter_factory');
        parent::setUp();
    }

    private function parseProducts($html)
    {
        return false;
        $products = [];
        $doc = new DOMDocument();
        $doc->loadHTML($html);
        foreach (css($doc, '.product') as $product) {
            $name = css($doc, '.name', $product)->item(0)->textContent;
            $price = css($doc, '.price', $product)->item(0)->textContent;
            $products[] = new ProductItem($name, $price);
        }
        return $products;
    }

    public function testDoesSupportMultipleExporters()
    {
        $exportPath = ETC_DIR . 'export-test/';
        if (!is_dir($exportPath)) {
            mkdir($exportPath, 0777, true);
        }

        $feature = $this->feedExportFeature
            ->withExporter($this->exporterFactory->createExporter('json', 'file://' . $exportPath . 'feed.json'))
            ->withExporter($this->exporterFactory->createExporter('csv', 'file://' . $exportPath . 'feed.csv'))
            ->withExporter($this->exporterFactory->createExporter('jsonlines', 'file://' . $exportPath . 'feed.jsonl'))
            ->withExporter($this->exporterFactory->createExporter('xml', 'file://' . $exportPath . 'feed.xml'));
        $this->runner
            ->withFeature($feature)
            ->withSpider(new TestSpider(['http://webshop.schrapert.dev/products.php'], function (ResponseInterface $response) {
                $html = (string)$response->getBody();
                $products = $this->parseProducts($html);
                return $products;
            }))
            ->start();
    }
}
