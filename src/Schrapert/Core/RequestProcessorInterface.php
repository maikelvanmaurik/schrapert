<?php
namespace Schrapert\Core;

use Schrapert\Crawl\RequestInterface;
use Schrapert\SpiderInterface;

interface RequestProcessorInterface
{
    /**
     * @param RequestInterface $request
     * @param SpiderInterface $spider
     * @return RequestProcessInterface
     */
    public function process(ExecutionEngine $engine, RequestInterface $request, SpiderInterface $spider);
}