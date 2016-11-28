<?php
namespace Schrapert\Http;

use Schrapert\Http\Downloader\Middleware\DownloadMiddlewareInterface;
use Schrapert\Http\Downloader\Middleware\ProcessRequestMiddlewareInterface;
use Schrapert\Http\Downloader\Middleware\ProcessResponseMiddlewareInterface;

class RedirectMiddleware implements DownloadMiddlewareInterface, ProcessRequestMiddlewareInterface, ProcessResponseMiddlewareInterface
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