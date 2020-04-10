<?php

namespace Schrapert\Http;

use Schrapert\Crawl\ResponseInterface as CrawlResponse;

interface ResponseInterface extends CrawlResponse, \Psr\Http\Message\ResponseInterface
{
}
