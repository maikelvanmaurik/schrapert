<?php

namespace Schrapert\Http\Downloader;

use React\Promise\PromiseInterface;
use Schrapert\Http\RequestInterface;

interface DownloaderInterface
{
    /**
     * @param RequestInterface $request
     * @return PromiseInterface
     */
    public function download(RequestInterface $request);
}
