<?php
namespace Schrapert\Http\Downloader;

use Schrapert\Http\RequestInterface;
use Schrapert\SpiderInterface;
use React\Promise\PromiseInterface;

interface DownloaderInterface
{
    /**
     * @param RequestInterface $request
     * @return PromiseInterface
     */
    public function download(RequestInterface $request);
}