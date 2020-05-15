<?php

namespace Schrapert\Http\Cookies;

use ArrayIterator;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Traversable;

/**
 * Cookie jar that stores cookies as an array.
 */
class CookieJar implements CookieJarInterface
{
    /**
     * @var CookieInterface[] Loaded cookie data
     */
    private array $cookies;

    /**
     * @var CookiePolicyInterface
     */
    private CookiePolicyInterface $policy;

    private CookieStringParserInterface $parser;

    /**
     * @param CookieInterface[]  $cookies  array of Cookie objects
     * @param CookiePolicyInterface  $policy
     * @param CookieStringParserInterface $parser
     */
    public function __construct(
        ?iterable $cookies = null,
        CookiePolicyInterface $policy = null,
        CookieStringParserInterface $parser = null
    ) {
        if (null === $policy) {
            $policy = new Rfc2616CookiePolicy();
        }
        if (null === $parser) {
            $parser = new CookieStringParser();
        }
        $this->policy = $policy;
        $this->parser = $parser;
        $this->cookies = [];
        if (null !== $cookies) {
            $this->setCookies($cookies);
        }
    }

    /**
     * Create a new Cookie jar from an associative array and domain.
     *
     * @param  array  $cookies  Cookies to create the jar from
     * @param  string  $domain  Domain to set the cookies to
     *
     * @return self
     */
    public static function fromArray(array $cookies, $domain)
    {
        $jar = new self;
        foreach ($cookies as $name => $value) {
            $jar->setCookie(new Cookie([
                'Domain' => $domain,
                'Name' => $name,
                'Value' => $value,
                'Discard' => true
            ]));
        }

        return $jar;
    }

    public function toArray()
    {
        return array_map(function (CookieInterface $cookie) {
            return $cookie->toArray();
        }, $this->getIterator()->getArrayCopy());
    }

    public function clear(?string $domain = null, ?string $path = null, ?string $name = null): void
    {
        if (null === $domain && null === $path && null === $name) {
            $this->cookies = [];
            return;
        }
        $this->cookies = array_filter(
            $this->cookies,
            function (CookieInterface $cookie) use ($domain, $path, $name) {
                return ((null !== $domain && $domain !== $cookie->getDomain())
                    || (null !== $path && $path !== $cookie->getPath())
                    || (null !== $name && $name !== $cookie->getName()));
            }
        );
    }

    public function getPolicy(): CookiePolicyInterface
    {
        return $this->policy;
    }

    /**
     * @param  CookiePolicyInterface  $policy
     * @return CookieJarInterface
     */
    public function withPolicy(CookiePolicyInterface $policy): CookieJarInterface
    {
        $clone = clone $this;
        $clone->policy = $policy;
        return $clone;
    }

    public function withParser(CookieStringParser $parser) : CookieJarInterface
    {
        $clone = clone $this;
        $clone->parser = $parser;
        return $clone;
    }

    public function clearSessionCookies(): void
    {
        foreach ($this->getIterator() as $cookie) {
            if ($cookie->getDiscard()) {
                $this->clear($cookie->getDomain(), $cookie->getPath(), $cookie->getName());
            }
        }
    }

    public function setCookies(iterable $cookies)
    {
        foreach ($cookies as $cookie) {
            if (!$cookie instanceof CookieInterface) {
                throw new \InvalidArgumentException("Invalid cookie");
            }
            $this->setCookie($cookie);
        }
    }

    protected function createCookieKey(CookieInterface $cookie)
    {

    }

    public function setCookie(CookieInterface $cookie): bool
    {
        $key = $this->createCookieKey($cookie);
        // Resolve conflicts with previously set cookies
        foreach ($this->cookies as $i => $c) {
            // Two cookies are identical, when their path, and domain are
            // identical.
            if ($c->getPath() !== $cookie->getPath() ||
                $c->getDomain() !== $cookie->getDomain() ||
                $c->getName() !== $cookie->getName()
            ) {
                continue;
            }

            // The previously set cookie is a discard cookie and this one is
            // not so allow the new cookie to be set
            if (!$cookie->getDiscard() && $c->getDiscard()) {
                unset($this->cookies[$i]);
                continue;
            }

            // If the new cookie's expiration is further into the future, then
            // replace the old cookie
            if ($cookie->getExpires() > $c->getExpires()) {
                unset($this->cookies[$i]);
                continue;
            }

            // If the value has changed, we better change it
            if ($cookie->getValue() !== $c->getValue()) {
                unset($this->cookies[$i]);
                continue;
            }

            // The cookie exists, so no need to continue
            return false;
        }

        $this->cookies[] = $cookie;

        return true;
    }

    public function setCookieIfAllowed(CookieInterface $cookie, RequestInterface $request): bool
    {
        if ($this->policy->isAllowed($cookie, $request)) {
            $this->setCookie($cookie);
        }
    }

    public function setCookiesIfAllowed(iterable $cookies, RequestInterface $request)
    {
        foreach ($cookies as $cookie) {
            $this->setCookieIfAllowed($cookie, $request);
        }
    }

    public function count()
    {
        return count($this->cookies);
    }

    /**
     * @return Traversable|CookieInterface[]
     */
    public function getIterator()
    {
        return new ArrayIterator(array_values($this->cookies));
    }

    /**
     * If a cookie already exists and the server asks to set it again with a
     * null value, the cookie must be deleted.
     *
     * @param  CookieInterface  $cookie
     */
    private function removeCookieIfEmpty(CookieInterface $cookie)
    {
        $cookieValue = $cookie->getValue();
        if ($cookieValue === null || $cookieValue === '') {
            $this->clear(
                $cookie->getDomain(),
                $cookie->getPath(),
                $cookie->getName()
            );
        }
    }

    public function extractCookies(ResponseInterface $response, RequestInterface $request)
    {
        $this->policy->extractCookies($response, $request, $this);
    }

    public function addCookieHeader(RequestInterface $request): RequestInterface
    {
        foreach ($this->policy->cookiesForRequest($this, $request) as $cookie) {

        }
    }
}
