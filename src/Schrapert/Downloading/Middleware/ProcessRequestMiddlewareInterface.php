<?php

namespace Schrapert\Downloading\Middleware;

use Schrapert\Http\RequestInterface;

interface ProcessRequestMiddlewareInterface
{
    /**
     * @param RequestInterface $request
     */
    public function processRequest(RequestInterface $request);
}
