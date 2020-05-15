<?php

namespace Schrapert\Downloading\Middleware;

use Schrapert\Downloading\RequestOptionsInterface;
use Schrapert\Downloading\RequestInterface;
use Schrapert\Downloading\ResponseInterface;
use Schrapert\Events\EventDispatcherInterface;
use Schrapert\Http\Cookies\CookieJarInterface;
use Schrapert\Http\Cookies\CookieJarManagerInterface;
use Schrapert\Http\RequestInterface as HttpRequestInterface;

class CookiesMiddleware
{
    private $cookieJars;

    private $events;

    public function __construct(EventDispatcherInterface $events, CookieJarManagerInterface $cookieJars)
    {
        $this->events = $events;
        $this->cookieJars = $cookieJars;
    }

    private function gatherRequestCookies(CookieJarInterface $cookieJar, HttpRequestInterface $request, RequestOptionsInterface $options)
    {
        if (null === ($cookies = $options->get('cookies'))) {
            return [];
        }

        if (is_array($cookies)) {
            $cookies = array_map(function ($k, $v) {
            }, array_keys($cookies), $cookies);
        }

        return $cookies;
    }

    public function __invoke(callable $next)
    {
        return function (RequestInterface $request, RequestOptionsInterface $options) use ($next) {
            if (! $request instanceof HttpRequestInterface) {
                return $next($request, $options);
            }
            if ($options->get('dont_merge_cookies', false)) {
                return $next($request, $options);
            }

            $jar = $options->get('cookiejar');

            if (! $jar instanceof CookieJarInterface) {
                $jar = $this->cookieJars[$jar];
            }

            $cookies = $this->gatherRequestCookies($jar, $request, $options);
            foreach ($cookies as $cookie) {
                $jar->setCookieIfOk($cookie, $request);
            }

            $cookies = $jar->getCookies($request);

            // Remove current cookie header
            $request = $request->withoutHeader('Cookie')
                ->withHeader('Cookie', $jar->generateHeader($request->getUri()->getHost()));
        };
    }

    /**
     * @param  RequestInterface  $request
     * @return RequestInterface
     */
    public function processRequest(RequestInterface $request)
    {
        $values = [];
        $uri = $request->getUri();
        $scheme = $uri->getScheme();
        $host = $uri->getHost();
        $path = $uri->getPath() ?: '/';

        foreach ($this->cookies->getIterator() as $cookie) {
            if ($cookie->matchesPath($path) &&
                $cookie->matchesDomain($host) &&
                !$cookie->isExpired() &&
                (!$cookie->getSecure() || $scheme === 'https')
            ) {
                $values[] = $cookie->getName().'='
                    .$cookie->getValue();
            }
        }

        if ($values) {
            $request = $request->withHeader('Cookie', implode('; ', $values));
        }

        return $request;
    }

    public function processResponse(ResponseInterface $response, RequestInterface $request)
    {
        if (null !== ($headers = $response->getHeader('Set-Cookie'))) {
            foreach ((array) $headers as $cookie) {
                $cookie = $this->parser->parse($cookie);
                if (!$cookie->getDomain()) {
                    $cookie->setDomain($request->getUri()->getHost());
                }
                $this->cookies->setCookie($cookie);
            }
        }
    }
}
