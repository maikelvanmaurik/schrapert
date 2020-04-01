<?php
namespace Schrapert\Http\Downloader\Event;

use Schrapert\Event\Event;
use Schrapert\Http\RequestInterface;
use DateTime;

class DownloadRequestEvent extends Event
{
    private $time;

    private $request;

    public function __construct(RequestInterface $request, DateTime $time)
    {
        parent::__construct('download-request');
        $this->request = $request;
        $this->time = $time;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getTime()
    {
        return $this->time;
    }
}