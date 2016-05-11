<?php
namespace Schrapert;

use React\SocketClient\Connector;
use React\SocketClient\DnsConnector;
use React\SocketClient\SecureConnector;
use React\SocketClient\TcpConnector;
use Schrapert\Configuration\ConfigurationInterface;
use Schrapert\Configuration\DefaultConfiguration;
use Schrapert\Core\ExecutionEngineFactory;
use Schrapert\Core\RequestProcessorFactory;
use Schrapert\Core\RequestProcessorFactoryInterface;
use Schrapert\Core\Scraper;
use Schrapert\Core\ScraperInterface;
use Schrapert\Filter\DuplicateFingerprintRequestFilter;
use Schrapert\Filter\DuplicateRequestFilterInterface;
use Schrapert\Http\ClientFactory;
use Schrapert\Http\Downloader\DownloaderBuilder;
use Schrapert\Http\Downloader\DownloaderBuilderInterface;
use Schrapert\Http\Downloader\DownloaderInterface;
use Schrapert\Http\Downloader\DownloadRequestFactory;
use Schrapert\Http\Request;
use Schrapert\Http\RequestProcessor;
use Schrapert\Http\ScrapeProcessFactory;
use Schrapert\IO\FileSystemClientFactory;
use Schrapert\Log\Logger;
use Schrapert\Log\LoggerInterface;
use Schrapert\Schedule\DiskQueue;
use Schrapert\Schedule\MemoryQueue;
use Schrapert\Schedule\PriorityQueue;
use Schrapert\Schedule\Scheduler;
use Schrapert\Signal\SignalManager;
use React\EventLoop\Factory as LoopFactory;
use Schrapert\Signal\SignalManagerInterface;
use Schrapert\Util\DelayedCallbackFactory;
use Schrapert\Util\IntervalCallbackFactory;
use Schrapert\Crawl\RequestFingerprintGenerator;

/**
 * The Schrapert project aims to follow a SOLID approach.
 * This builder can be used to generate a runner from
 * configuration, but you are free to compose the runner in a
 * way that suits your needs.
 */
class RunnerBuilder
{
    private $logger;

    private $downloader;

    private $scraper;

    private $signals;

    private $loop;

    private $dnsResolver;

    private $httpClientFactory;

    private $configuration;

    private $downloaderBuilder;

    private $requestProcessorFactory;

    private $duplicateRequestFilter;

    public function __construct()
    {

    }

    public function getConfiguration()
    {
        if(null === $this->configuration) {
            $this->configuration = new DefaultConfiguration();
        }
        return $this->configuration;
    }

    public function setConfiguration(ConfigurationInterface $config)
    {
        $this->configuration = $config;
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getLogger()
    {
        if(null === $this->logger) {
            $this->logger = new Logger($this->getConfiguration()->getSetting('LOG_PATH'), intval($this->getConfiguration()->getSetting('LOG_LEVEL')));
        }
        return $this->logger;
    }

    public function getEventLoop()
    {
        if(null === $this->loop) {
            $this->loop = LoopFactory::create();
        }
        return $this->loop;
    }

    public function getSignalManager()
    {
        if(null === $this->signals) {
            $this->signals = new SignalManager();
        }
        return $this->signals;
    }

    public function setSignalManager(SignalManagerInterface $signals)
    {
        $this->signals = $signals;
    }

    public function getDnsResolver()
    {
        if(null === $this->dnsResolver) {
            $dnsResolverFactory = new \React\Dns\Resolver\Factory();
            $this->dnsResolver = $dnsResolverFactory->create('8.8.8.8', $this->getEventLoop());
            //$this->dnsResolver = $dnsResolverFactory->createCached('127.0.0.1', $this->getEventLoop());
        }
        return $this->dnsResolver;
    }

    public function getHttpClientFactory()
    {
        if(null === $this->httpClientFactory) {
            $httpClientFactory = new \React\HttpClient\Factory();
            $this->httpClientFactory = new ClientFactory($this->getEventLoop(), $this->getDnsResolver(), $httpClientFactory);
        }
        return $this->httpClientFactory;
    }

    public function setHttpClientFactory(ClientFactory $factory)
    {
        $this->httpClientFactory = $factory;
    }


    public function getScraper()
    {
        if(null === $this->scraper) {
            $this->scraper = new Scraper($this->getLogger(), $this->getEventLoop());
        }
        return $this->scraper;
    }

    public function setScraper(ScraperInterface $scraper)
    {
        $this->scraper = $scraper;
    }

    public function setDownloader(DownloaderInterface $downloader)
    {
        $this->downloader = $downloader;
    }

    public function setDownloaderBuilder(DownloaderBuilderInterface $downloaderBuilder)
    {
        $this->downloaderBuilder = $downloaderBuilder;
    }

    public function getDownloaderBuilder()
    {
        if(null == $this->downloaderBuilder) {
            $builder = new DownloaderBuilder();
            $connector = new DnsConnector(new TcpConnector($this->getEventLoop()), $this->getDnsResolver());
            $secureConnector = new SecureConnector($connector, $this->getEventLoop());
            $builder->setDownloadRequestFactory(new DownloadRequestFactory($connector, $secureConnector));
            $builder->setHttpClientFactory($this->getHttpClientFactory());
            $builder->setLogger($this->getLogger());
            $builder->setConfiguration($this->getConfiguration());
            $this->downloaderBuilder = $builder;
        }
        return $this->downloaderBuilder;
    }

    public function getDownloader()
    {
        if(null == $this->downloader) {
            $this->downloader = $this->getDownloaderBuilder()->build();
        }
        return $this->downloader;
    }

    public function getRequestProcessorFactory()
    {
        if(null === $this->requestProcessorFactory) {
            $requestProcessorFactory = new RequestProcessorFactory();
            $requestProcessorFactory->register(Request::class, function() {
                $downloadProcessFactory = new ScrapeProcessFactory($this->getLogger(), $this->getDownloader(), $this->getScraper());
                return new RequestProcessor($downloadProcessFactory);
            });
            $this->requestProcessorFactory = $requestProcessorFactory;
        }
        return $this->requestProcessorFactory;
    }

    public function setRequestProcessorFactory(RequestProcessorFactoryInterface $requestProcessorFactory)
    {
        $this->requestProcessorFactory = $requestProcessorFactory;
    }

    public function getDuplicateRequestFilter()
    {
        if(null === $this->duplicateRequestFilter) {
            $this->duplicateRequestFilter = new DuplicateFingerprintRequestFilter(new RequestFingerprintGenerator());
        }
        return $this->duplicateRequestFilter;
    }

    public function setDuplicateRequestFilter(DuplicateRequestFilterInterface $filter)
    {
        $this->duplicateRequestFilter = $filter;
    }

    /**
     * @return \Schrapert\Runner
     */
    public function build()
    {
        $fsFactory = new FileSystemClientFactory($this->getEventLoop());

        $priorityQueue = new PriorityQueue(new MemoryQueue(), new DiskQueue($this->getLogger(), $fsFactory->factory(), $this->getConfiguration()->getSetting('SCHEDULER_DISK_PATH')));

        $delayedCallbackFactory = new DelayedCallbackFactory($this->getEventLoop());
        $intervalCallbackFactory = new IntervalCallbackFactory($this->getEventLoop());

        $scheduler = new Scheduler($this->getLogger(), $priorityQueue);

        $engineFactory = new ExecutionEngineFactory($this->getLogger(), $this->getSignalManager(), $this->getScraper(), $this->getDuplicateRequestFilter(), $this->getRequestProcessorFactory(), $scheduler, $intervalCallbackFactory, $delayedCallbackFactory);

        return new Runner($this->getEventLoop(), $this->getSignalManager(), $engineFactory);
    }
}
