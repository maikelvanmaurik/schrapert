<?php
namespace Schrapert\Http;

use Schrapert\Crawl\RequestInterface as CrawlRequest;

interface RequestInterface extends CrawlRequest
{
    public function getUri();

    public function on($event, callable $callback);

    public function getPort();

    public function getHost();

    public function getProtocol();

    public function getHeaders();

    public function getQueryString();

    public function getPath();

    public function getProtocolVersion();

    public function setHeader($name, $value);

    public function getMethod();

    public function setMethod($method);

    public function end($data = null);
}