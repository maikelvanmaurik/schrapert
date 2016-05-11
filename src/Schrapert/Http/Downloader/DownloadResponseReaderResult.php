<?php
namespace Schrapert\Http\Downloader;

class DownloadResponseReaderResult
{
    public function __construct(DownloadResponse $response, $body, $error)
    {
        $this->response = $response;
        $this->body = $body;
        $this->error = $error;
    }

    public function __toString()
    {
        return (string)$this->body;
    }
}