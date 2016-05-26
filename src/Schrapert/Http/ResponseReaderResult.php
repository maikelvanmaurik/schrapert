<?php
namespace Schrapert\Http;

class ResponseReaderResult implements ResponseReaderResultInterface
{
    public function __construct(ResponseInterface $response, $body, $error)
    {
        $this->response = $response;
        $this->body = $body;
        $this->error = $error;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getError()
    {
        return $this->error;
    }

    public function __toString()
    {
        return (string)$this->body;
    }
}