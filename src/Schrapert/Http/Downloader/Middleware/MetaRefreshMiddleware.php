<?php
namespace Schrapert\Http;

use Schrapert\Http\Downloader\Middleware\DownloadMiddlewareInterface;
use Schrapert\Http\Downloader\Middleware\ProcessResponseMiddlewareInterface;

class MetaRefreshMiddleware implements DownloadMiddlewareInterface, ProcessResponseMiddlewareInterface
{
    public function processResponse(ResponseInterface $response, RequestInterface $request)
    {
        // TODO: Implement processResponse() method.
    }
}