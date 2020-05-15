<?php

namespace Schrapert\Downloading\Middleware;

use Schrapert\Downloading\RequestInterface;

interface DownloaderMiddlewareInterface
{
    public function handle(RequestInterface $request, callable $next);
}
