<?php
namespace Schrapert\Http\Downloader;

use Schrapert\Http\RequestInterface;
use Schrapert\SpiderInterface;
use React\Promise\PromiseInterface;

interface DownloaderInterface
{
    /**
     * @param RequestInterface $request
     * @param SpiderInterface $spider
     * @return PromiseInterface
     */
    public function fetch(RequestInterface $request, SpiderInterface $spider);

    public function needsBackOut();
}