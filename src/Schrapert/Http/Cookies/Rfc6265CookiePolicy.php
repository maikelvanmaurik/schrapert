<?php
namespace Schrapert\Http\Cookies;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Rfc6265CookiePolicy implements CookiePolicyInterface
{
    private array $blockedDomains;

    private array $securedSchemes;

    private CookieStringParserInterface $parser;

    public function __construct(CookieStringParserInterface $parser = null, $securedSchemes = ['https', 'wss'])
    {
        if (null === $parser) {
            $parser = new CookieStringParser();
        }
        $this->securedSchemes = (array)$securedSchemes;
        $this->parser = $parser;
    }

    public function extractCookies(ResponseInterface $response, RequestInterface $request, CookieJarInterface $jar)
    {
        $cookies = $response->getHeader('Set-Cookie');

        if (! $cookies) {
            return;
        }

        foreach ($cookies as $str) {
            $cookie = new Cookie();
            $this->parser->parse($str, $cookie);
            /*
            if (!$cookie->getDomain()) {
                $cookie->setDomain($request->getUri()->getHost());
            }
            */
            if (0 !== strpos($cookie->getPath(), '/')) {
                $cookie->setPath($this->getCookiePathFromRequest($request));
            }
            $jar->setCookie($cookie);
        }
    }

    public function withBlockedDomains(array $blockedDomains)
    {
        $clone = clone $this;
        $clone->blockedDomains = $blockedDomains;
        return $clone;
    }

    public function isAllowed(CookieInterface $cookie, RequestInterface $request) : bool
    {
        foreach (['version', 'verifiability', 'name', 'path', 'domain', 'port'] as $property) {
            $check = 'isAllowed'.ucfirst($property);
            if (! $this->$check($cookie, $request)) {
                return false;
            }
        }
        return true;
    }

    public function isAllowedVerifiability(CookieInterface $cookie, RequestInterface $request) : bool
    {
        return true;
    }

    public function isAllowedVersion(CookieInterface $cookie, RequestInterface $request) : bool
    {
        return true;
    }

    public function isAllowedName(CookieInterface $cookie, RequestInterface $request)
    {
        // If the name string is empty (but not 0), ignore the set-cookie
        // string entirely.
        $name = $cookie->getName();
        if (! $name && $name !== '0') {
            return false;
        }
        return false;
    }

    public function setAllowed(CookieInterface $cookie, RequestInterface $request): bool
    {
        // TODO: Implement setAllowed() method.
    }

    public function returnAllowed(CookieInterface $cookie, RequestInterface $request): bool
    {
        // TODO: Implement returnAllowed() method.
    }

    /**
     * Computes cookie path following RFC 6265 section 5.1.4
     *
     * @link https://tools.ietf.org/html/rfc6265#section-5.1.4
     */
    private function getCookiePathFromRequest(RequestInterface $request): string
    {
        $uriPath = $request->getUri()->getPath();
        if ('' === $uriPath) {
            return '/';
        }
        if (0 !== \strpos($uriPath, '/')) {
            return '/';
        }
        if ('/' === $uriPath) {
            return '/';
        }
        if (0 === $lastSlashPos = \strrpos($uriPath, '/')) {
            return '/';
        }

        return \substr($uriPath, 0, $lastSlashPos);
    }

    public function isDomainValidForReturn($domain, RequestInterface $request)
    {
        // Remove the leading '.' as per spec in RFC 6265.
        // https://tools.ietf.org/html/rfc6265#section-5.2.3
        $domain = \ltrim($domain, '.');
        $host = $request->getUri()->getHost();
        if (0 === \strcasecmp($domain, $host)) {
            return true;
        }

        // IP address is not allowed as per spec in RFC 6265.
        // https://tools.ietf.org/html/rfc6265#section-5.1.3
        if (\filter_var($host, FILTER_VALIDATE_IP)) {
            return false;
        }

        return (bool) \preg_match('/\.' . \preg_quote($domain, '/') . '$/', $host);
    }

    public function isPathValidForReturn($path, RequestInterface $request)
    {
        return true;
    }

    /**
     * @param  iterable|CookieInterface[]  $cookies
     * @param  RequestInterface  $request
     * @return iterable
     */
    public function cookiesForRequest(iterable $cookies, RequestInterface $request): iterable
    {
        $matches = [];
        foreach ($cookies as $cookie) {
            if (!$this->isDomainValidForReturn($cookie->getDomain(), $request)) {
                continue;
            }
            if (!$this->isPathValidForReturn($cookie->getPath(), $request)) {
                continue;
            }
            if ($cookie->isSecure() && !in_array($request->getUri()->getScheme(), $this->securedSchemes)) {
                continue;
            }
            $matches[] = $cookie;
        }
        return $matches;
    }
}