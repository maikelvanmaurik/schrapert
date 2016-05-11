<?php
class SimpleCompressedDownloadSpider extends \Schrapert\Spider
{
    protected $urls;

    private $logger;

    protected $startUris = [
        'http://robotstxt.schrapert.dev/'
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

        $this->logger->debug("Parse simple spider");
        if(!$response instanceof \Schrapert\Http\ResponseInterface) {
            //return;
        }

        var_dump((string)$response->getBody());

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
        $this->workingDir = sys_get_temp_dir().'/robots-middleware-test/';
        rmdir_recursive($this->workingDir);
    }

    public function _testDoesTakeRobotsTxtIntoAccount()
    {
        $builder = new \Schrapert\RunnerBuilder();

        $spider = new SimpleCompressedDownloadSpider($builder->getLogger(), new \Schrapert\Http\Util\Uri());
        $config = new \Schrapert\Configuration\DefaultConfiguration();
        $config->setSetting('HTTP_DOWNLOAD_DECORATORS', [
            'Schrapert\Http\Downloader\Decorator\CompressDownloadDecorator' => 1
        ]);

        $builder->setConfiguration($config);

        $runner = $builder->build();
        $runner->addSpider($spider);
        $runner->start();

        $notAllowedVisits = [];

        foreach($spider->visited as $visited) {
            $uri = $visited->getUri();
            if($uri == 'http://robotstxt.schrapert.dev/disallowed-for-bad-bot/') {
                $notAllowedVisits[] = $uri;
            }
            if(0 === strpos($uri, 'http://robotstxt.schrapert.dev/private/')) {
                $notAllowedVisits[] = $uri;
            }
        }

        $this->assertEmpty($notAllowedVisits, sprintf('BadBot visited %s which was not allowed for the bot', implode(', ', $notAllowedVisits)));
    }
}