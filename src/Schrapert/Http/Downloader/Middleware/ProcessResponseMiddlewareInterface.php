<?php

namespace Schrapert\Http\Downloader\Middleware;

use Schrapert\Http\RequestInterface;
use Schrapert\Http\ResponseInterface;

interface ProcessResponseMiddlewareInterface
{
    public function processResponse(ResponseInterface $response, RequestInterface $request);
}
