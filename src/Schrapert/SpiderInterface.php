<?php
namespace Schrapert;

use Schrapert\Crawl\ResponseInterface;

interface SpiderInterface
{
    public function parse(ResponseInterface $response);

    /**
     * @return \Schrapert\Crawl\RequestInterface[]
     */
    public function startRequests();
}