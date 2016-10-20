<?php
namespace Schrapert\Http\Downloader;

use Schrapert\Http\ResponseInterface;
use Schrapert\Http\ResponseProvidingBodyInterface;

class DownloadResponse implements ResponseInterface, ResponseProvidingBodyInterface
{
    public function __construct(DownloadRequest $request, $protocol, $version, $code, $reasonPhrase, $headers, $body)
    {
        $this->request = $request;
        $this->protocol = $protocol;
        $this->version = $version;
        $this->code = $code;
        $this->reasonPhrase = $reasonPhrase;
        $this->headers = $headers;
        $this->body = $body;
    }

    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->request->getUri();
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getHeader($name, $default = null)
    {
        return array_key_exists($name, $this->headers) ? $this->headers[$name] : $default;
    }

    public function getBody()
    {
        return $this->body;
    }
}