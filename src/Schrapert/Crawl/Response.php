<?php
namespace Schrapert\Crawl;

class Response implements ResponseInterface
{
    private $body;

    private $request;

    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    public function __toString()
    {
        return (string)$this->body;
    }
}