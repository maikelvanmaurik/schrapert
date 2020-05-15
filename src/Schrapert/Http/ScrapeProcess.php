<?php

namespace Schrapert\Http;

use React\Promise\PromiseInterface;
use RuntimeException;
use Schrapert\Core\ExecutionEngine;
use Schrapert\Core\RequestProcessInterface;
use Schrapert\Core\ScraperInterface;
use Schrapert\Crawling\CrawlerInterface;
use Schrapert\Downloading\DownloaderInterface;
use Schrapert\Log\LoggerInterface;

/**
 * Represents a scraping process where the request is downloaded and passed to the
 * scraper for parsing.
 *
 * @package Schrapert\Http
 */
class ScrapeProcess implements RequestProcessInterface
{
    /**
     * @var DownloaderInterface
     */
    private $downloader;
    /**
     * @var CrawlerInterface
     */
    private $crawler;
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var ScraperInterface
     */
    private $scraper;
    /**
     * @var ExecutionEngine
     */
    private $engine;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        LoggerInterface $logger,
        ExecutionEngine $engine,
        DownloaderInterface $downloader,
        ScraperInterface $scraper,
        RequestInterface $request,
        CrawlerInterface $crawler
    ) {
        $this->logger = $logger;
        $this->engine = $engine;
        $this->downloader = $downloader;
        $this->scraper = $scraper;
        $this->request = $request;
        $this->crawler = $crawler;
    }

    public function needsBackOut()
    {
        return false;
    }

    /**
     * @return PromiseInterface
     */
    public function run()
    {
        if (! $this->request instanceof RequestInterface) {
            throw new RuntimeException('Request for downloads need to be http requests');
        }

        $this->logger->debug('Scrape process: start {uri}', ['uri' => $this->request->getUri()]);

        // Download the request
        return $this->downloader->download($this->request)->then(function ($response) {
            // Feed the response to the scraper
            return $this->scraper->enqueueScrape($this->engine, $this->request, $response, $this->spider);
        });
    }
}
