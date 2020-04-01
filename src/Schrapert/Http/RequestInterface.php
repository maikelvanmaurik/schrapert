<?php
namespace Schrapert\Http;

use Schrapert\Crawl\RequestInterface as CrawlRequest;

interface RequestInterface extends CrawlRequest, \Psr\Http\Message\RequestInterface
{

}