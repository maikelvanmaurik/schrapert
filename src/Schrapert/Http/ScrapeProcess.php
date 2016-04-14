<?php
namespace Schrapert\Http;

use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use React\Promise\RejectedPromise;
use Schrapert\Core\ExecutionEngine;
use Schrapert\Core\RequestProcessInterface;
use Schrapert\Core\ScraperInterface;
use Schrapert\Http\Downloader\DownloaderInterface;
use RuntimeException;
use Schrapert\Http\Downloader\DownloadResponseReader;
use Schrapert\Log\LoggerInterface;
use Schrapert\SpiderInterface;
use Exception;

/**
 * Represents a scraping process where the request is downloaded and passed to the
 * scraper for parsing
 *
 * @package Schrapert\Http
 */
class ScrapeProcess implements RequestProcessInterface
{
    private $downloader;

    private $spider;

    private $request;

    private $scraper;

    private $engine;

    private $logger;

    public function __construct(LoggerInterface $logger, ExecutionEngine $engine, DownloaderInterface $downloader, ScraperInterface $scraper, RequestInterface $request, SpiderInterface $spider)
    {
        $this->logger = $logger;
        $this->engine = $engine;
        $this->downloader = $downloader;
        $this->scraper = $scraper;
        $this->request = $request;
        $this->spider = $spider;
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
        if (!$this->request instanceof RequestInterface) {
            throw new RuntimeException("Request for downloads need to be http requests");
        }

        // Download the request
        return $this->downloader->fetch($this->request, $this->spider)->then(function($response) {

            $reader = new DownloadResponseReader();
            return $reader->readToEnd($response)->then(function($body) use ($response) {
                $response = new Response($this->request->getUri(), $body, $response->getProtocol(), $response->getProtocolVersion(), $response->getCode(), $response->getReasonPhrase(), $response->getHeaders());

                // Feed the response to the scraper
                return $this->scraper->enqueueScrape($this->engine, $this->request, $response, $this->spider);

            });
        });
    }
}