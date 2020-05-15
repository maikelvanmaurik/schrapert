<?php

namespace Schrapert\Downloading\Middleware;

use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use Schrapert\Crawling\Exceptions\IgnoreRequestException;
use Schrapert\Downloading\DownloaderInterface;
use Schrapert\Downloading\RequestOptions;
use Schrapert\Downloading\RequestOptionsInterface;
use Schrapert\Downloading\RequestInterface;
use Schrapert\Downloading\ResponseInterface;
use Schrapert\Events\EventDispatcherInterface;
use Schrapert\Http\RequestInterface as HttpRequestInterface;
use Schrapert\Http\RobotsTxt\ParseResultInterface;
use Schrapert\Http\RobotsTxt\ParserInterface as RobotsTxtParserInterface;

class RobotsTxtDownloaderMiddleware
{
    /**
     * @var DownloaderInterface
     */
    private DownloaderInterface $downloader;
    /**
     * @var EventDispatcherInterface
     */
    private EventDispatcherInterface $events;
    /**
     * @var RobotsTxtParserInterface
     */
    private RobotsTxtParserInterface $robotsTxtParser;
    /**
     * @var string|null
     */
    private ?string $userAgent;

    const DEFAULT_USER_AGENT = 'Schrapert';

    public function __construct(
        EventDispatcherInterface $events,
        DownloaderInterface $downloader,
        RobotsTxtParserInterface $robotsTxtParser,
        ?string $userAgent
    ) {
        $this->userAgent = $userAgent;
        $this->events = $events;
        $this->robotsTxtParser = $robotsTxtParser;
        $this->downloader = $downloader;
    }

    public function __invoke(callable $next)
    {
        return function (RequestInterface $request, RequestOptionsInterface $options) use ($next) {
            if ($options->get('ignore_robots_txt', false)) {
                return $next($request, $options);
            }

            $ua = $this->userAgent;

            if (!$ua && $request instanceof HttpRequestInterface) {
                $ua = $request->getHeaderLine('User-Agent');
            }

            if (!$ua) {
                $ua = self::DEFAULT_USER_AGENT;
            }

            return $this->parseRobotsTxt($request)->then(function (ParseResultInterface $result) use (
                $next,
                $request,
                $options,
                $ua
            ) {
                if ($result->isAllowed($ua, $request->getUri()->getPath())) {
                    return $next($request, $options);
                } else {
                    // $this->events->dispatch(new RobotsTxtResourceNotAllowed($request, $ua));
                    /*
                    $this->logger->debug("Resource '{path}' not allowed for user-agent '{ua}' when obeying robots.txt, drop request",
                        [
                            'path' => $request->getUri()->getPath(),
                            'ua' => $ua
                        ]);
                    */
                    throw new IgnoreRequestException('Not allowed');
                }
            });
        };
    }

    /**
     * @param  RequestInterface  $request
     * @return PromiseInterface
     */
    public function parseRobotsTxt(RequestInterface $request) : PromiseInterface
    {
        $robotsRequest = $request->withUri($request->getUri()->withPath('/robots.txt'));
        $options = new RequestOptions(['ignore_robots_txt' => true]);
        return $this->downloader->download($robotsRequest, $options)->then(function (ResponseInterface $response) {
            $txt = (string) $response->getBody();
            return $this->robotsTxtParser->parse($txt);
        });
    }
}
