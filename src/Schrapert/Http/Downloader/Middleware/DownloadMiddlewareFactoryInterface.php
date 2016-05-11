<?php
namespace Schrapert\Http\Downloader\Middleware;

use Schrapert\Http\Downloader\DownloaderInterface;

interface DownloadMiddlewareFactoryInterface
{
    public function factory($type, DownloaderInterface $downloader);
}