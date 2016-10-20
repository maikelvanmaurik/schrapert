<?php
namespace Schrapert\DependencyInjection;

use React\Filesystem\Filesystem;
use React\HttpClient\Client;
use React\SocketClient\DnsConnector;
use React\SocketClient\SecureConnector;
use React\SocketClient\TcpConnector;
use Schrapert\Core\ExecutionEngine;
use Schrapert\Core\RequestProcessorFactory;
use Schrapert\Core\Scraper;
use Schrapert\Crawl\RequestFingerprintGenerator;
use Schrapert\Filter\DuplicateFingerprintRequestFilter;
use Schrapert\Http\Cookies\CookieJar;
use Schrapert\Http\Cookies\SetCookieParser;
use Schrapert\Http\Downloader\Downloader;
use Schrapert\Http\Downloader\DownloadTransactionFactory;
use Schrapert\Http\Downloader\Middleware\CookiesMiddleware;
use Schrapert\Http\PathNormalizer;
use Schrapert\Http\RequestDispatcher;
use Schrapert\Http\ResponseFactory;
use Schrapert\Http\RobotsTxt\Parser as RobotsTxtParser;
use Schrapert\Http\Downloader\DownloadRequestFactory;
use Schrapert\Http\Downloader\Middleware\RobotsTxtDownloadMiddleware;
use Schrapert\Http\Request as HttpRequest;
use Schrapert\Http\RequestProcessor;
use Schrapert\Http\ScrapeProcessFactory;
use Schrapert\Http\StreamFactory;
use Schrapert\Http\UriFactory;
use Schrapert\Http\UriResolver;
use Schrapert\IO\FileSystemClient;
use Schrapert\Log\Logger;
use Schrapert\Runner;
use React\EventLoop\Factory as LoopFactory;
use Schrapert\Schedule\DiskQueue;
use Schrapert\Schedule\MemoryQueue;
use Schrapert\Schedule\PriorityQueue;
use Schrapert\Schedule\Scheduler;
use Schrapert\Signal\SignalManager;
use Schrapert\Util\DelayedCallbackFactory;
use Schrapert\Util\IntervalCallbackFactory;

class DefaultServiceContainer extends ServiceContainer
{
    public function __construct()
    {
        parent::__construct();
        $this->registerDefaults();
    }

    private function registerDefaults()
    {
        $this->set('event_loop', function() {
            return LoopFactory::create();
        });

        $this->set('signal_manager', function() {
            return new SignalManager();
        });

        $this->set('logger', function() {
            return new Logger();
        });


        $this->set('scraper', function() {
            return new Scraper($this->get('logger'), $this->get('event_loop'));
        });

        $this->set('request_fingerprint_generator', function() {
            return new RequestFingerprintGenerator();
        });


        $this->set('duplicate_request_filter', function() {
            return new DuplicateFingerprintRequestFilter(
                $this->get('request_fingerprint_generator')
            );
        });

        $this->set('filesystem_client', function() {
            return new FileSystemClient(Filesystem::create(
                $this->get('event_loop')
            ));
        });

        $this->set('dns_resolver', function() {
            $factory = new \React\Dns\Resolver\Factory();
            return $factory->create('8.8.8.8', $this->get('event_loop'));
        });

        $this->set('download_request_factory', function() {
            $connector = new DnsConnector(new TcpConnector($this->get('event_loop')), $this->get('dns_resolver'));
            $secureConnector = new SecureConnector($connector, $this->get('event_loop'));
            return new DownloadRequestFactory($connector, $secureConnector);
        });

        $this->set('http_connector', function() {
            return new DnsConnector(new TcpConnector($this->get('event_loop')), $this->get('dns_resolver'));
        });

        $this->set('http_secure_connector', function() {
            return new SecureConnector($this->get('http_connector'), $this->get('event_loop'));
        });

        $this->set('http_client', function() {
           return new Client(
                $this->get('http_connector'),
                $this->get('http_secure_connector')
           );
        });

        $this->set('http_response_factory', function() {
            return new ResponseFactory(
                $this->get('http_stream_factory')
            );
        });

        $this->set('request_dispatcher', function() {
            return new RequestDispatcher(
                $this->get('http_client'),
                $this->get('http_response_factory')
            );
        });

        $this->set('download_transaction_factory', function() {
            return new DownloadTransactionFactory(
                $this->get('request_dispatcher'),
                $this->get('http_uri_resolver'),
                $this->get('http_stream_factory')
            );
        });

        $this->set('downloader', function() {
            return new Downloader(
                $this->get('logger'),
                $this->get('download_transaction_factory')
            );
        });

        $this->set('robots_txt_parser', function() {
           return new RobotsTxtParser();
        });

        $this->set('downloader_middleware_robots_txt', function() {
            return new RobotsTxtDownloadMiddleware(
                $this->get('downloader'),
                $this->get('robots_txt_parser'),
                $this->get('logger')
            );
        });

        $this->set('downloader_middleware_cookies', function() {
            return new CookiesMiddleware(
                $this->get('http_cookie_jar'),
                $this->get('http_set_cookie_parser')
            );
        });

        $this->set('http_cookie_jar', function() {
           return new CookieJar();
        });

        $this->set('http_set_cookie_parser', function() {
           return new SetCookieParser();
        });


        $this->set('scrape_process_factory', function() {
            return new ScrapeProcessFactory(
                $this->get('logger'),
                $this->get('downloader'),
                $this->get('scraper'),
                $this->get('response_reader_factory')
            );
        });

        $this->set('request_processor_factory', function() {
            $factory = new RequestProcessorFactory();
            // Register default type(s)
            $factory->register(HttpRequest::class, function() {
                return new RequestProcessor($this->get('scrape_process_factory'));
            });
            return $factory;
        });

        $this->set('memory_queue', function() {
            return new MemoryQueue();
        });

        $this->set('disk_queue', function() {
            return new DiskQueue(
                $this->get('logger'),
                $this->get('filesystem_client')
            );
        });

        $this->set('priority_queue', function() {
            return new PriorityQueue(
                $this->get('memory_queue'),
                $this->get('disk_queue')
            );
        });

        $this->set('scheduler', function() {
            return new Scheduler(
                $this->get('logger'),
                $this->get('priority_queue')
            );
        });

        $this->set('interval_callback_factory', function() {
            return new IntervalCallbackFactory(
                $this->get('event_loop')
            );
        });

        $this->set('delayed_callback_factory', function() {
            return new DelayedCallbackFactory(
                $this->get('event_loop')
            );
        });

        $this->set('path_normalizer', function() {
           return new PathNormalizer();
        });

        $this->set('uri_factory', function() {
           return new UriFactory();
        });

        $this->set('http_stream_factory', function() {
           return new StreamFactory();
        });

        $this->set('http_uri_resolver', function() {
            return new UriResolver(
                $this->get('uri_factory'),
                $this->get('path_normalizer')
            );
        });

        $this->set('engine', function() {
            return new ExecutionEngine(
                $this->get('logger'),
                $this->get('signal_manager'),
                $this->get('scraper'),
                $this->get('duplicate_request_filter'),
                $this->get('request_processor_factory'),
                $this->get('scheduler'),
                $this->get('interval_callback_factory'),
                $this->get('delayed_callback_factory')
            );
        });

        $this->set('crawler_runner', function() {
            return new Runner(
                $this->get('event_loop'),
                $this->get('signal_manager'),
                $this->get('engine'));
            }
        );
    }
}