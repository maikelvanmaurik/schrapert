<?php
namespace Schrapert\Http\Downloader\Exception;

use Schrapert\Crawl\RequestInterface;
use Schrapert\Http\Downloader\Downloader;

class DownloaderTimeoutException extends \RuntimeException
{
    private $request;

    public function __construct(Downloader $downloader, RequestInterface $request)
    {
        parent::__construct('Download timeout');
        $this->downloader = $downloader;
        $this->request = $request;
    }

    public function getRequest()
    {
        return $this->request;
    }
}