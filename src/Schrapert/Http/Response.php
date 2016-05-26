<?php

namespace Schrapert\Http;


class Response implements ResponseInterface, ResponseProvidingBodyInterface
{
    public function __construct($uri, $body, $protocol, $version, $code, $reasonPhrase, $headers)
    {
        $this->uri = $uri;
        $this->body = $body;
        $this->protocol = $protocol;
        $this->version = $version;
        $this->code = $code;
        $this->reasonPhrase = $reasonPhrase;
        $this->headers = $headers;
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getProtocol()
    {
        return $this->protocol;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getReasonPhrase()
    {
        return $this->reasonPhrase;
    }

    public function getHeaders()
    {
        return $this->headers;
    }
}