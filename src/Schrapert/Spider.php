<?php
namespace Schrapert;

use Schrapert\Http\Request;
use Schrapert\Crawl\ResponseInterface;

abstract class Spider implements SpiderInterface
{
    protected $startUris = [];

    public function startRequests()
    {
        foreach($this->startUris as $uri) {
            yield new Request($uri);
        }

    }

    /**
     * @param ResponseInterface $response
     * @return \Generator|\Iterator
     */
    public abstract function parse(ResponseInterface $response);
}