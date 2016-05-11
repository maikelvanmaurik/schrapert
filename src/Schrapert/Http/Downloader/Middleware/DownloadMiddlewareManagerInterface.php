<?php
namespace Schrapert\Http\Downloader\Middleware;

interface DownloadMiddlewareManagerInterface
{
    public function addMiddleware(DownloadMiddlewareInterface $middleware);
}