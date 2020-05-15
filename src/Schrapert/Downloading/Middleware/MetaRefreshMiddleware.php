<?php

namespace Schrapert\Http;

use Schrapert\Downloading\Middleware\DownloaderMiddlewareInterface;
use Schrapert\Downloading\Middleware\ProcessResponseMiddlewareInterface;

class MetaRefreshMiddleware implements DownloaderMiddlewareInterface, ProcessResponseMiddlewareInterface
{
    public function processResponse(ResponseInterface $response, RequestInterface $request)
    {
        // TODO: Implement processResponse() method.
    }
}
