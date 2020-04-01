<?php
namespace Schrapert\Http;

use Exception;

class ResponseException extends Exception
{
    private $response;

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }
}