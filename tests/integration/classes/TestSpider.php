<?php
namespace Schrapert\Test\Integration;

use Schrapert\Spider;

class TestSpider extends Spider
{
    private $parseCallback;

    public function __construct(array $startUris = [], callable $parseCallback)
    {
        $this->startUris = $startUris;
        $this->parseCallback = $parseCallback;
    }

    /**
     * @param \Schrapert\Crawl\ResponseInterface $response
     * @return \Generator|\Iterator
     */
    public function parse(\Schrapert\Crawl\ResponseInterface $response)
    {
        return call_user_func($this->parse);
    }
}