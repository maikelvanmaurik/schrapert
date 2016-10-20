<?php
namespace Schrapert\Http\Downloader\Middleware;

use Schrapert\Http\RequestInterface;
use Schrapert\SpiderInterface;

interface ProcessRequestMiddlewareInterface
{
    /**
     * @param RequestInterface $request
     */
    public function processRequest(RequestInterface $request);
}