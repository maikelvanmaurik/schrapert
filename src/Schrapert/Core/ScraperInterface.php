<?php
namespace Schrapert\Core;

use Schrapert\Crawl\RequestInterface;
use Schrapert\Crawl\ResponseInterface;
use Schrapert\SpiderInterface;
use React\Promise\PromiseInterface;

interface ScraperInterface
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param SpiderInterface $spider
     * @return PromiseInterface
     */
    public function enqueueScrape(ExecutionEngine $engine, RequestInterface $request, ResponseInterface $response, SpiderInterface $spider);

    public function open(SpiderInterface $spider);

    public function isIdle();
}