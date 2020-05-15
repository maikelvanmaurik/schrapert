<?php

namespace Schrapert\Http;

use Schrapert\Downloading\Middleware\DownloaderMiddlewareInterface;
use Schrapert\Downloading\Middleware\ProcessRequestMiddlewareInterface;
use Schrapert\Downloading\Middleware\ProcessResponseMiddlewareInterface;

class RedirectMiddleware implements DownloaderMiddlewareInterface, ProcessRequestMiddlewareInterface, ProcessResponseMiddlewareInterface
{
    /**
     * @param RequestInterface $request
     */
    public function processRequest(RequestInterface $request)
    {
        // TODO: Implement processRequest() method.
    }

    public function processResponse(ResponseInterface $response, RequestInterface $request)
    {
        // TODO: Implement processResponse() method.
    }
}
