<?php
namespace Schrapert\Http;

class ResponseBuilder implements ResponseBuilderInterface
{
    private $body;

    private $headers;

    private $uri;

    private $version;

    private $code;

    private $phrase;

    private $protocol;

    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    public function setHeaders($headers)
    {
        $this->headers = $headers;
        return $this;
    }

    public function setUri($uri)
    {
        $this->uri = $uri;
        return $this;
    }

    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    public function setReasonPhrase($phrase)
    {
        $this->phrase = $phrase;
        return $this;
    }

    /**
     * @return ResponseInterface
     */
    public function build()
    {
        return new Response($this->uri, $this->body, $this->protocol, $this->version, $this->code, null, $this->headers);
    }

}