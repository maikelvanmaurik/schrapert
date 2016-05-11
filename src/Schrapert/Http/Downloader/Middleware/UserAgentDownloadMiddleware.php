<?php
namespace Schrapert\Http\Downloader\Middleware;

use Schrapert\Http\Downloader\DownloaderInterface;
use Schrapert\Http\RequestInterface;
use Schrapert\SpiderInterface;

class UserAgentDownloadMiddleware implements DownloaderInterface
{
    private $downloader;

    private $userAgent;

    public function __construct(DownloaderInterface $downloader, $userAgent = 'Schrapert')
    {
        $this->downloader = $downloader;
        $this->userAgent = $userAgent;
    }

    public function fetch(RequestInterface $request, SpiderInterface $spider)
    {
        if($request instanceof RequestInterface) {
            $request->setHeader('User-Agent', $this->userAgent);
        }

        return $this->downloader->fetch($request, $spider);
    }

    public function needsBackOut()
    {
        return $this->downloader->needsBackOut();
    }
}