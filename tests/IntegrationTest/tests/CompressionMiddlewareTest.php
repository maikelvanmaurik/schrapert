<?php
class SimpleCompressedDownloadSpider extends \Schrapert\Spider
{
    protected $urls;

    private $logger;

    protected $startUris = [
        'http://compression.schrapert.dev/'
    ];

    public $visited;

    public function __construct(\Schrapert\Log\LoggerInterface $logger, \Schrapert\Http\Util\Uri $uri)
    {
        $this->logger = $logger;
        $this->uri = $uri;
        $this->urls = [];
        $this->visited = [];
    }

    /**
     * @param \Schrapert\Crawl\ResponseInterface $response
     * @return \Generator|\Iterator
     */
    public function parse(\Schrapert\Crawl\ResponseInterface $response)
    {
        $this->visited[] = $response;
        $doc = new DOMDocument('1.0');
        $doc->loadHTML((string)$response->getBody());
        $xpath = new DOMXPath($doc);
        $nodes = $xpath->query('//a');

        foreach($nodes as $node) {
            /* @var $node \DOMElement */
            $request = new \Schrapert\Http\Request();
            $request->setUri($this->uri->join($node->getAttribute('href'), $response->getUri()));
            yield $request;
        }
    }
}

class CompressedDownloadMiddlewareTest extends PHPUnit_Framework_TestCase
{
    private $workingDir;

    public function setUp()
    {
        parent::setUp();
        $this->workingDir = sys_get_temp_dir().'/compressed-download-test/';
        rmdir_recursive($this->workingDir);
    }

    public function testCompressionAndDecompressionIsWorking()
    {
        $builder = new \Schrapert\RunnerBuilder();

        $spider = new SimpleCompressedDownloadSpider($builder->getLogger(), new \Schrapert\Http\Util\Uri());
        $config = new \Schrapert\Configuration\DefaultConfiguration();
        $config->setSetting('SCHEDULER_DISK_PATH', $this->workingDir);
        $config->setSetting('HTTP_DOWNLOAD_MIDDLEWARE', [
            'Schrapert\Http\Downloader\Middleware\DownloadCompressionMiddleware' => 1
        ]);
        $builder->setConfiguration($config);
        $runner = $builder->build();
        $runner->addSpider($spider);
        $runner->start();


        foreach($spider->visited as $response) {
            $reader = new \Schrapert\Http\ResponseReader($response);
            $reader->readToEnd()->then(function(\Schrapert\Http\ResponseReaderResult $result) {
                $body = $result->getBody();
                $this->assertArrayHasKey('Content-Enconding', $result->getResponse()->getHeaders());
                $this->assertStringStartsWith('This content is compressed using gzip encoding. ', $body);
            });
        }
    }
}