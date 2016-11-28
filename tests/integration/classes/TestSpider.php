<?php
namespace Schrapert\Test\Integration;

use Schrapert\Crawl\ResponseInterface;
use Schrapert\Spider;

class TestSpider extends Spider
{
    private $parseCallback;

    public function __construct(array $startUris = [], callable $parseCallback)
    {
        $this->startUris = $startUris;
        $this->parseCallback = $parseCallback;
    }

    public function getName()
    {
        return 'test-spider';
    }

    /**
     * @param \Schrapert\Crawl\ResponseInterface $response
     * @return \Generator|\Iterator
     */
    public function parse(ResponseInterface $response)
    {
        return call_user_func($this->parseCallback, $response);
    }
}