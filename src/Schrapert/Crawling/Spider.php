<?php

namespace Schrapert\Crawling;

use Schrapert\Downloading\ResponseInterface;
use Schrapert\Http\Request;

abstract class Spider implements CrawlerInterface
{
    protected $startUris = [];

    public function startRequests()
    {
        foreach ($this->startUris as $uri) {
            yield new Request($uri);
        }
    }

    /**
     * @param ResponseInterface $response
     * @return \Generator|\Iterator
     */
    abstract public function parse(ResponseInterface $response);
}
