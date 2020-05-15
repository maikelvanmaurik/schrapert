<?php

namespace Schrapert\Downloading;

class Response extends Message implements ResponseInterface
{
    private $body;

    public function __construct($body = '')
    {
        parent::__construct();
        $this->setBody($body);
    }

    public function setBody($body)
    {
        $this->body = $body;
    }

    public function getBody()
    {
        return $this->body;
    }
}
