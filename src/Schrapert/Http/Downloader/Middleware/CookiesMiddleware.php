<?php
namespace Schrapert\Http\Downloader\Middleware;

use Schrapert\Http\Cookies\CookieJarInterface;
use Schrapert\Http\Cookies\SetCookieParserInterface;
use Schrapert\Http\RequestInterface;
use Schrapert\Http\ResponseInterface;

class CookiesMiddleware implements DownloadMiddlewareInterface, ProcessRequestMiddlewareInterface, ProcessResponseMiddlewareInterface
{
    private $cookies;

    private $parser;

    public function __construct(CookieJarInterface $cookies, SetCookieParserInterface $parser)
    {
        $this->cookies = $cookies;
        $this->parser = $parser;
    }

    /**
     * @param RequestInterface $request
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
                $values[] = $cookie->getName() . '='
                    . $cookie->getValue();
            }
        }

        if($values) {
            $request = $request->withHeader('Cookie', implode('; ', $values));
        }

        return $request;
    }

    public function processResponse(ResponseInterface $response, RequestInterface $request)
    {
        if (null !== ($headers = $response->getHeader('Set-Cookie'))) {
            foreach ((array)$headers as $cookie) {
                $cookie = $this->parser->parse($cookie);
                if (!$cookie->getDomain()) {
                    $cookie->setDomain($request->getUri()->getHost());
                }
                $this->cookies->setCookie($cookie);
            }
        }
    }
}