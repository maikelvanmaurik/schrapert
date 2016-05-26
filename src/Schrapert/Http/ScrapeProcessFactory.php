<?php
namespace Schrapert\Http;

use Schrapert\Core\ExecutionEngine;
use Schrapert\Core\ScraperInterface;
use Schrapert\Http\Downloader\DownloaderInterface;
use Schrapert\Log\LoggerInterface;
use Schrapert\SpiderInterface;

class ScrapeProcessFactory
{
    private $downloader;

    private $scraper;

    private $logger;

    private $responseReaderFactory;

    public function __construct(LoggerInterface $logger, DownloaderInterface $downloader, ScraperInterface $scraper, ResponseReaderFactoryInterface $readerFactory)
    {
        $this->logger = $logger;
        $this->scraper = $scraper;
        $this->downloader = $downloader;
        $this->responseReaderFactory = $readerFactory;
    }

    public function factory(ExecutionEngine $engine, RequestInterface $request, SpiderInterface $spider)
    {
        return new ScrapeProcess($this->logger, $engine, $this->downloader, $this->scraper, $request, $spider, $this->responseReaderFactory);
    }
}