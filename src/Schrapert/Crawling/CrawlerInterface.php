<?php

namespace Schrapert\Crawling;

interface CrawlerInterface
{
    /**
     * @return string
     */
    public function getName();

    public function parse(ResponseInterface $response);

    /**
     * @return \Schrapert\Downloading\RequestInterface[]
     */
    public function startRequests();
}
