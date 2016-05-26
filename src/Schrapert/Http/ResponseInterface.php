<?php
namespace Schrapert\Http;

use Schrapert\Crawl\ResponseInterface as CrawlResponse;

interface ResponseInterface extends CrawlResponse
{
    /**
     * @return string
     */
    public function getUri();

    public function getHeaders();
}