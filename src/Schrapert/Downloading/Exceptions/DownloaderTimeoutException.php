<?php

namespace Schrapert\Downloading\Exceptions;

use Schrapert\Downloading\DownloaderInterface;
use Schrapert\Downloading\RequestInterface;

class DownloaderTimeoutException extends \RuntimeException
{
    private RequestInterface $request;

    private DownloaderInterface $downloader;

    public function __construct(DownloaderInterface $downloader, RequestInterface $request)
    {
        $this->downloader = $downloader;
        $this->request = $request;
        parent::__construct();
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getDownloader()
    {
        return $this->downloader;
    }
}
