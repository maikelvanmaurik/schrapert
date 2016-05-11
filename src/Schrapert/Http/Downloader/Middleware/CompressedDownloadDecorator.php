<?php
namespace Schrapert\Http\Downloader\Decorator;

use Schrapert\Http\Downloader\DownloaderInterface;
use Schrapert\Http\Downloader\DownloadResponseReader;
use Schrapert\Http\Downloader\DownloadResponseReaderResult;
use Schrapert\Http\Request;
use Schrapert\Http\RequestInterface;
use Schrapert\Log\LoggerInterface;
use Schrapert\SpiderInterface;
use React\Promise\PromiseInterface;

class CompressedDownloadDecorator implements DownloaderInterface
{
    private $downloader;

    private $logger;

    public function __construct(DownloaderInterface $downloader, LoggerInterface $logger, $userAgent = 'Schrapert')
    {
        $this->logger = $logger;
        $this->userAgent = $userAgent;
        $this->downloader = $downloader;
    }

    public function fetch(RequestInterface $request, SpiderInterface $spider)
    {
        $request->setHeader('Accept-Encoding', 'gzip,deflate');
        return $this->downloader->fetch($request, $spider)->then(function($response) {
            $reader = new DownloadResponseReader();
            return $reader->readToEnd($response);
        })->then(function(DownloadResponseReaderResult $result) {



           var_dump((string)$result);
            die();
        });
    }

    /**
     * @param RequestInterface $request
     * @return PromiseInterface
     */
    public function parseRobotsTxt(RequestInterface $request, SpiderInterface $spider)
    {
        $robotsRequest = new Request();
        $parsed = parse_url($request->getUri());
        $uri = $parsed['scheme'] . '://' . $parsed['host'] . (!empty($parsed['port']) ? ':' . $parsed['port'] : '') . '/robots.txt';
        $robotsRequest->setUri($uri);

        return $this->downloader->fetch($robotsRequest, $spider)->then(function ($response) {

            $reader = new DownloadResponseReader();
            return $reader->readToEnd($response);

        })->then(function ($txt) {

            return $this->robotsTxtParser->parse($txt);
        });
    }

    public function needsBackOut()
    {
        return $this->downloader->needsBackOut();
    }
}