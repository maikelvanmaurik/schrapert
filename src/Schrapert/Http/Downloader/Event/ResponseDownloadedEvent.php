<?php
namespace Schrapert\Http\Downloader\Event;

use Schrapert\Event\Event;
use Schrapert\Http\Downloader\DownloaderInterface;
use Schrapert\Http\RequestInterface;
use Schrapert\Http\ResponseInterface;

class ResponseDownloadedEvent extends Event
{
    private $downloader;

    private $response;

    private $request;

    public function __construct(DownloaderInterface $downloader, ResponseInterface $response, RequestInterface $request)
    {
        parent::__construct('response-downloaded');
        $this->downloader = $downloader;
        $this->response = $response;
        $this->request = $request;
    }

    public function getDownloader()
    {
        return $this->downloader;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getRequest()
    {
        return $this->request;
    }
}