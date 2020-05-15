<?php

namespace Schrapert\DI;

use React\EventLoop\Factory as LoopFactory;
use React\Filesystem\Filesystem;
use React\HttpClient\Client;
use React\SocketClient\DnsConnector;
use React\SocketClient\SecureConnector;
use React\SocketClient\TcpConnector;
use Schrapert\Core\ExecutionEngine;
use Schrapert\Core\RequestProcessorFactory;
use Schrapert\Core\Scraper;
use Schrapert\Downloading\Downloader;
use Schrapert\Downloading\DownloaderInterface;
use Schrapert\Downloading\Middleware\CompressionMiddleware;
use Schrapert\Downloading\Middleware\ConcurrentRequestLimitMiddleware;
use Schrapert\Downloading\Middleware\CookiesMiddleware;
use Schrapert\Downloading\Middleware\DefaultHeadersMiddleware;
use Schrapert\Downloading\Middleware\HttpCacheMiddleware;
use Schrapert\Downloading\Middleware\RetryMiddleware;
use Schrapert\Downloading\Middleware\RobotsTxtDownloaderMiddleware;
use Schrapert\Downloading\RequestFingerprintGenerator;
use Schrapert\Downloading\TransactionFactory;
use Schrapert\Events\EventDispatcher;
use Schrapert\Events\EventDispatcherInterface;
use Schrapert\Feature\AutoThrottleFeature;
use Schrapert\Feature\FeedExportFeature;
use Schrapert\Feed\ExporterFactory;
use Schrapert\Feed\StorageFactory;
use Schrapert\Filter\DuplicateFingerprintRequestFilter;
use Schrapert\Http\Cache\DummyPolicy;
use Schrapert\Http\Cache\FileStorage;
use Schrapert\Http\Cache\PdoStorage;
use Schrapert\Http\Cache\Rfc2616Policy;
use Schrapert\Http\Cookies\CookieJar;
use Schrapert\Http\Cookies\CookieJarFactory;
use Schrapert\Http\Cookies\CookieJarFactoryInterface;
use Schrapert\Http\Cookies\CookieJarManager;
use Schrapert\Http\Cookies\CookieJarManagerInterface;
use Schrapert\Http\Cookies\CookieParser;
use Schrapert\Http\PathNormalizer;
use Schrapert\Http\Request as HttpRequest;
use Schrapert\Http\RequestDispatcher;
use Schrapert\Http\RequestProcessor;
use Schrapert\Http\ResponseFactory;
use Schrapert\Http\RobotsTxt\Parser as RobotsTxtParser;
use Schrapert\Http\ScrapeProcessFactory;
use Schrapert\Http\StreamFactory;
use Schrapert\Http\UriFactory;
use Schrapert\Http\UriResolver;
use Schrapert\IO\FileSystemClient;
use Schrapert\Log\Logger;
use Schrapert\Runner;
use Schrapert\Scheduling\DiskQueue;
use Schrapert\Scheduling\MemoryQueue;
use Schrapert\Scheduling\PriorityQueue;
use Schrapert\Scheduling\Scheduler;
use Schrapert\Scraping\ItemPipeline;
use Schrapert\Util\DelayedCallbackFactory;
use Schrapert\Util\IntervalCallbackFactory;

class DefaultContainer extends Container
{
    public function __construct()
    {
        parent::__construct();
        $this->registerDefaults();
    }

    private function registerDefaults()
    {
        $this->bind('loop', function () {
            return LoopFactory::create();
        });

        $this->bind(EventDispatcherInterface::class, function () {
            return new EventDispatcher();
        });

        $this->bind('logger', function () {
            return new Logger();
        });

        $this->bind('item_pipeline', function () {
            return new ItemPipeline();
        });

        $this->bind('scraper', function (ContainerInterface $container) {
            return new Scraper(
                $container['logger'],
                $container['loop'],
                $container[EventDispatcherInterface::class],
                $container['item.pipeline']
            );
        });

        $this->bind('request.fingerprint.generator', function () {
            return new RequestFingerprintGenerator();
        });

        $this->bind('request.dupe.filter', function () {
            return new DuplicateFingerprintRequestFilter(
                $this->get('request_fingerprint_generator')
            );
        });

        $this->bind('feed.exporter.factory', function () {
            return new ExporterFactory(new StorageFactory());
        });

        $this->bind('feed.export.feature', function (ContainerInterface $container) {
            return new FeedExportFeature(
                $container['events']
            );
        });

        $this->bind('throttle_feature', function (ContainerInterface $container) {
            return new AutoThrottleFeature(
                $container['events'],
                $container['logger']
            );
        });

        $this->bind('filesystem_client', function (ContainerInterface $container) {
            return new FileSystemClient(Filesystem::create(
                $container['loop']
            ));
        });

        $this->bind('dns.resolver', function (ContainerInterface $container) {
            $factory = new \React\Dns\Resolver\Factory();
            return $factory->createCached('8.8.8.8', $container['loop']);
        });

        $this->bind('download_request_factory', function () {
            $connector = new DnsConnector(new TcpConnector($this->get('event_loop')), $this->get('dns.resolver'));
            $secureConnector = new SecureConnector($connector, $this->get('event_loop'));
            return new DownloadRequestFactory($connector, $secureConnector);
        });

        $this->bind('http.connector', function (ContainerInterface $container) {
            return new DnsConnector(
                new TcpConnector(
                    $container['loop']
                ),
                $container['dns.resolver']
            );
        });

        $this->bind('http.secure.connector', function (ContainerInterface $container) {
            return new SecureConnector(
                $container['http.connector'],
                $container['loop']
            );
        });

        $this->bind('http.client', function () {
            return new Client(
                $this->get('http.connector'),
                $this->get('http.secure.connector')
            );
        });

        $this->bind('http.cache.file.storage', function (ContainerInterface $container) {
            return new FileStorage(
                $container['request.fingerprint.generator'],
                $container['http.stream.factory']
            );
        });

        $this->bind('http.cache.pdo.storage', function (ContainerInterface $container) {
            return new PdoStorage(
                $container['request.fingerprint.generator']
            );
        });

        $this->bind('http.cache.policy.dummy', function () {
            return new DummyPolicy();
        });

        $this->bind('http.cache.policy.rfc2616', function () {
            return new Rfc2616Policy();
        });

        $this->bind('http.response.factory', function () {
            return new ResponseFactory(
                $this->get('http_stream_factory')
            );
        });

        $this->bind('request.dispatcher', function () {
            return new RequestDispatcher(
                $this->get('http.client'),
                $this->get('http.response.factory')
            );
        });

        $this->bind('download.transaction.factory', function (ContainerInterface $container) {
            return new TransactionFactory(
                $container['loop'],
                $container['request.dispatcher'],
                $container['http.uri.resolver'],
                $container['http.stream.factory']
            );
        });

        $this->bind(DownloaderInterface::class, function (ContainerInterface $container) {
            return new Downloader(
                $container['events'],
                $container['logger'],
                $container['download.transaction.factory']
            );
        });

        $this->bind(RobotsTxtParser::class, function () {
            return new RobotsTxtParser();
        });

        $this->bind(RobotsTxtDownloaderMiddleware::class, function (ContainerInterface $container) {
            return new RobotsTxtDownloaderMiddleware(
                $container[EventDispatcherInterface::class],
                $container[DownloaderInterface::class],
                $container[RobotsTxtParser::class],
                $container[ConfigurationInterface::class]->get('user-agent')
            );
        });

        $this->bind('downloader_middleware_concurrent_request_limit', function (ContainerInterface $container) {
            return new ConcurrentRequestLimitMiddleware(
                $container['events'],
                $container['logger'],
                $container['delayed.callback.factory'],
                $container['dns.resolver']
            );
        });

        $this->bind('downloader_retry_middleware', function () {
            return new RetryMiddleware(
                $this->get('logger')
            );
        });

        $this->bind('downloader_middleware_http_cache', function () {
            return new HttpCacheMiddleware(
                $this->get('http_cache_file_storage'),
                $this->get('http_cache_dummy_policy')
            );
        });

        $this->bind(CookieJarFactoryInterface::class, function () {
            return new CookieJarFactory();
        });

        $this->singleton(CookieJarManagerInterface::class, function () {
            return new CookieJarManager(
                $this->get(CookieJarFactoryInterface::class)
            );
        });

        $this->bind(CookiesMiddleware::class, function () {
            return new CookiesMiddleware(
                $this->get(EventDispatcherInterface::class),
                $this->get(CookieJarManagerInterface::class),
            );
        });

        $this->bind('downloader_middleware_default_headers', function () {
            return new DefaultHeadersMiddleware(
                $this->get('logger'),
                $this->get('http_stream_factory')
            );
        });

        $this->bind('downloader_middleware_compression', function () {
            return new CompressionMiddleware(
                $this->get('logger'),
                $this->get('http_stream_factory')
            );
        });

        $this->bind('http_cookie_jar', function () {
            return new CookieJar();
        });

        $this->bind('http_set_cookie_parser', function () {
            return new CookieParser();
        });

        $this->bind('scrape_process_factory', function () {
            return new ScrapeProcessFactory(
                $this->get('logger'),
                $this->get('downloader'),
                $this->get('scraper')
            );
        });

        $this->bind('request_processor_factory', function () {
            $factory = new RequestProcessorFactory();
            // Register default type(s)
            $factory->register(HttpRequest::class, function () {
                return new RequestProcessor($this->get('scrape_process_factory'));
            });
            return $factory;
        });

        $this->bind('memory_queue', function () {
            return new MemoryQueue();
        });

        $this->bind('disk_queue', function () {
            return new DiskQueue(
                $this->get('logger'),
                $this->get('filesystem_client')
            );
        });

        $this->bind('priority_queue', function () {
            return new PriorityQueue(
                $this->get('memory_queue'),
                $this->get('disk_queue')
            );
        });

        $this->bind('scheduler', function () {
            return new Scheduler(
                $this->get('logger'),
                $this->get('priority_queue')
            );
        });

        $this->bind('interval_callback_factory', function () {
            return new IntervalCallbackFactory(
                $this->get('event_loop')
            );
        });

        $this->bind('delayed_callback_factory', function () {
            return new DelayedCallbackFactory(
                $this->get('event_loop')
            );
        });

        $this->bind('path_normalizer', function () {
            return new PathNormalizer();
        });

        $this->bind('uri_factory', function () {
            return new UriFactory();
        });

        $this->bind('http_stream_factory', function () {
            return new StreamFactory();
        });

        $this->bind('http_uri_resolver', function () {
            return new UriResolver(
                $this->get('uri_factory'),
                $this->get('path_normalizer')
            );
        });

        $this->bind('engine', function () {
            return new ExecutionEngine(
                $this->get('logger'),
                $this->get('event_dispatcher'),
                $this->get('scraper'),
                $this->get('request.dupe.filter'),
                $this->get('request_processor_factory'),
                $this->get('scheduler'),
                $this->get('interval_callback_factory'),
                $this->get('delayed_callback_factory')
            );
        });

        $this->bind('crawler_runner', function () {
            return new Runner(
                $this->get('event_loop'),
                $this->get('event_dispatcher'),
                $this->get('engine')
            );
        });
    }
}
