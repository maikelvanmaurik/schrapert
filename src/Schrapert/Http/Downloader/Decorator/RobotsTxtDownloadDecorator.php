<?php
namespace Schrapert\Http\Downloader\Decorator;

use React\Promise\Deferred;
use React\Promise\FulfilledPromise;
use Schrapert\Crawl\Exception\IgnoreRequestException;
use Schrapert\Http\Downloader\DownloaderInterface;
use Schrapert\Http\Downloader\DownloadResponseReader;
use Schrapert\Http\Request;
use Schrapert\Http\RequestInterface;
use Schrapert\Http\RobotsTxt\ParserInterface as RobotsTxtParserInterface;
use Schrapert\Http\RobotsTxt\ParseResultInterface;
use Schrapert\Log\LoggerInterface;
use Schrapert\SpiderInterface;
use React\Promise\PromiseInterface;

class RobotsTxtDownloadDecorator implements DownloaderInterface
{
    private $downloader;

    private $userAgent;

    private $logger;

    private $robotsTxtParser;

    public function __construct(DownloaderInterface $downloader, RobotsTxtParserInterface $robotsTxtParser, LoggerInterface $logger, $userAgent = 'Schrapert')
    {
        $this->logger = $logger;
        $this->robotsTxtParser = $robotsTxtParser;
        $this->userAgent = $userAgent;
        $this->downloader = $downloader;
    }

    public function fetch(RequestInterface $request, SpiderInterface $spider)
    {
        if ($request->getMetaData('ignore_robots.txt') || !$request instanceof RequestInterface) {
            return $this->downloader->fetch($request, $spider);
        }

        return $this->parseRobotsTxt($request, $spider)->then(function (ParseResultInterface $result) use ($request, $spider) {
            if ($result->isAllowed($this->userAgent, $request->getPath())) {
                return $this->downloader->fetch($request, $spider);
            } else {
                $this->logger->debug("Resource %s not allowed for user-agent %s when obeying robots.txt, drop request", [$request->getPath(), $this->userAgent]);
                throw new IgnoreRequestException("Not allowed");
            }
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