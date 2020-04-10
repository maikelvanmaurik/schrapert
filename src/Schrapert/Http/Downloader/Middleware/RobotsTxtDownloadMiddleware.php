<?php

namespace Schrapert\Http\Downloader\Middleware;

use React\Promise\PromiseInterface;
use Schrapert\Crawl\Exception\IgnoreRequestException;
use Schrapert\Http\Downloader\DownloaderInterface;
use Schrapert\Http\RequestInterface;
use Schrapert\Http\ResponseInterface;
use Schrapert\Http\RobotsTxt\ParseResultInterface;
use Schrapert\Http\RobotsTxt\ParserInterface as RobotsTxtParserInterface;
use Schrapert\Log\LoggerInterface;

class RobotsTxtDownloadMiddleware implements DownloadMiddlewareInterface, ProcessRequestMiddlewareInterface
{
    private $downloader;

    private $logger;

    private $robotsTxtParser;

    public function __construct(DownloaderInterface $downloader, RobotsTxtParserInterface $robotsTxtParser, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->robotsTxtParser = $robotsTxtParser;
        $this->downloader = $downloader;
    }

    public function processRequest(RequestInterface $request)
    {
        if ($request->getMetadata('ignore_robots.txt') || ! $request instanceof RequestInterface || $request->getUri()->getPath() == '/robots.txt') {
            return $request;
        }

        $ua = $request->getHeaderLine('User-Agent');

        return $this->parseRobotsTxt($request)->then(function (ParseResultInterface $result) use ($request, $ua) {
            if ($result->isAllowed($ua, $request->getUri()->getPath())) {
                return $request;
            } else {
                $this->logger->debug("Resource '{path}' not allowed for user-agent '{ua}' when obeying robots.txt, drop request", [
                    'path' => $request->getUri()->getPath(),
                    'ua' => $ua,
                ]);
                throw new IgnoreRequestException('Not allowed');
            }
        });
    }

    /**
     * @param RequestInterface $request
     * @return PromiseInterface
     */
    public function parseRobotsTxt(RequestInterface $request)
    {
        $robotsRequest = $request
            ->withUri($request->getUri()->withPath('/robots.txt'))
            ->withMetadata('ignore_robots.txt', true);

        return $this->downloader->download($robotsRequest)->then(function (ResponseInterface $response) {
            $txt = (string) $response->getBody();

            return $this->robotsTxtParser->parse($txt);
        });
    }
}
