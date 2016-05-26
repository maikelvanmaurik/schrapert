<?php
class SimpleSpider extends \Schrapert\Spider
{
    protected $urls;

    private $logger;

    protected $startUris = [
        'http://robotstxt.schrapert.dev/'
    ];

    public $visited;

    public function __construct(\Schrapert\Log\LoggerInterface $logger, \Schrapert\Http\Util\Uri $uri, \Schrapert\Http\ResponseReaderFactory $readerFactory)
    {
        $this->logger = $logger;
        $this->uri = $uri;
        $this->readerFactory = $readerFactory;
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

        $this->readerFactory->factory($response)->readToEnd()->then(function(\Schrapert\Http\ResponseReaderResultInterface $result) {

            $response = $result->getResponse();

            $doc = new DOMDocument('1.0');
            $doc->loadHTML((string)$result);
            $xpath = new DOMXPath($doc);
            $nodes = $xpath->query('//a');

            foreach($nodes as $node) {
                /* @var $node \DOMElement */
                $request = new \Schrapert\Http\Request();
                $request->setUri($this->uri->join($node->getAttribute('href'), $response->getUri()));
                yield $request;
            }
        });
    }
}

class RobotsMiddlewareTest extends PHPUnit_Framework_TestCase
{
    private $workingDir;

    public function setUp()
    {
        parent::setUp();
        $this->workingDir = sys_get_temp_dir().'/robots-middleware-test/';
        rmdir_recursive($this->workingDir);
    }

    public function testDoesTakeDelayIntoAccount()
    {
        // build the runner
        // create a spider
        // run the spider
        // check timings
    }

    public function testDoesTakeRestrictedPagesIntoAccount()
    {
        $builder = new \Schrapert\RunnerBuilder();

        $spider = new SimpleSpider($builder->getLogger(), new \Schrapert\Http\Util\Uri(), new \Schrapert\Http\ResponseReaderFactory());
        $config = new \Schrapert\Configuration\DefaultConfiguration();
        $config->setSetting('USER_AGENT', 'BadBot');
        $config->setSetting('SCHEDULER_DISK_PATH', $this->workingDir);
        $config->setSetting('HTTP_DOWNLOAD_MIDDLEWARE', [
            'Schrapert\Http\Downloader\Middleware\RobotsTxtDownloadMiddleware' => 1
        ]);

        $builder->setConfiguration($config);

        $runner = $builder->build();
        $runner->addSpider($spider);
        $runner->start();

        $notAllowedVisits = $requiredVisits = [];

        foreach($spider->visited as $visited) {
            $uri = $visited->getUri();
            if($uri == 'http://robotstxt.schrapert.dev/disallowed-for-bad-bot/') {
                $notAllowedVisits[] = $uri;
            }
            if(0 === strpos($uri, 'http://robotstxt.schrapert.dev/private/')) {
                $notAllowedVisits[] = $uri;
            }
            if(0 === stripos($uri, 'http://robotstxt.schrapert.dev/public/')) {
                $requiredVisits[] = $uri;
            }
        }

        $this->assertNotEmpty($requiredVisits);
        $this->assertEmpty($notAllowedVisits, sprintf('BadBot visited %s which was not allowed for the bot', implode(', ', $notAllowedVisits)));
    }
}