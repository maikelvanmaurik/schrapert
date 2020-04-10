<?php

namespace Schrapert\Http\Downloader\Middleware;

use Schrapert\Http\RequestInterface;

interface ProcessRequestMiddlewareInterface
{
    /**
     * @param RequestInterface $request
     */
    public function processRequest(RequestInterface $request);
}
