<?php

namespace Schrapert\Downloading\Middleware;

use Schrapert\Downloading\DownloaderInterface;

interface DownloadMiddlewareFactoryInterface
{
    public function factory($type, DownloaderInterface $downloader);
}
