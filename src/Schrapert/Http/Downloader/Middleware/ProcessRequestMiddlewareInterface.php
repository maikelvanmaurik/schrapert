<?php
namespace Schrapert\Http\Downloader\Middleware;

use Schrapert\Http\RequestInterface;
use Schrapert\SpiderInterface;

interface ProcessRequestMiddlewareInterface
{
    /**
     * @param RequestInterface $request
     * @param SpiderInterface $spider
     */
    public function processRequest(RequestInterface $request, SpiderInterface $spider);
}