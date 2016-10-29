<?php
namespace Schrapert\Crawl;

class Response extends Message implements ResponseInterface
{
    private $body;

    public function __construct()
    {

    }

    public function __toString()
    {
        return (string)$this->body;
    }
}