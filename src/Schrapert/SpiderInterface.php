<?php
namespace Schrapert;

use Schrapert\Crawl\ResponseInterface;

interface SpiderInterface
{
    /**
     * @return string
     */
    public function getName();

    public function parse(ResponseInterface $response);

    /**
     * @return \Schrapert\Crawl\RequestInterface[]
     */
    public function startRequests();
}