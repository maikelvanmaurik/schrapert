<?php
namespace Schrapert\Http;

use Schrapert\Core\ExecutionEngine;
use Schrapert\Core\RequestProcessorInterface;
use Schrapert\Crawl\RequestInterface as CrawlRequest;
use Schrapert\SpiderInterface;
use RuntimeException;

/**
 * Request processor for HTTP requests
 *
 * @package Schrapert\Http
 */
class RequestProcessor implements RequestProcessorInterface
{
    private $scrapeProcessFactory;

    public function __construct(ScrapeProcessFactory $scrapeProcessFactory)
    {
        $this->scrapeProcessFactory = $scrapeProcessFactory;
    }

    public function process(ExecutionEngine $engine, CrawlRequest $request, SpiderInterface $spider)
    {
        if(!$request instanceof RequestInterface) {
            throw new RuntimeException("Processor should be used for http requests");
        }
        return $this->scrapeProcessFactory->factory($engine, $request, $spider);
    }
}