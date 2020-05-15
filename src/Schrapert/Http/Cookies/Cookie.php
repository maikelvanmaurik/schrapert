<?php

namespace Schrapert\Http\Cookies;

use DateTimeInterface;

use function Schrapert\str_studly;

/**
 * Cookie object.
 */
class Cookie implements CookieInterface
{
    private ?string $name;

    private ?string $value;

    private ?int $port;

    private ?DateTimeInterface $expires;

    private ?bool $httpOnly;

    private ?string $domain;

    private ?string $path;

    private ?bool $discard;

    private ?int $maxAge;

    private ?int $version;

    private ?string $sameSite;

    private ?bool $secure;

    public function __construct(
        ?string $name = null,
        ?string $value = null,
        ?string $domain = null,
        ?string $path = null,
        ?DateTimeInterface $expires = null,
        ?bool $discard = null,
        ?bool $httpOnly = null,
        ?bool $secure = null
    ) {
        $this->name = $name;
        $this->value = $value;
        $this->path = $path;
        $this->domain = $domain;
        $this->secure = $secure;
        $this->expires = $expires;
        $this->httpOnly = $httpOnly;
        $this->discard = $discard;
    }

    public function __toString()
    {
        $str = $this->name.'='.$this->value.'; ';
        if ($this->expires) {
            $str .= 'Expires='.$this->expires->format('D, d M Y H:i:s \G\M\T').'; ';
        }
        return rtrim($str, '; ');
    }

    public static function fromArray(array $array)
    {
        $cookie = new self;

        foreach ($array as $k => $v) {
            $method = 'with'.str_studly($k);
            if (method_exists($cookie, $method)) {
                $cookie = $cookie->$method($v);
            }
        }

        return $cookie;
    }

    /**
     * Get the cookie name.
     *
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set the cookie name.
     *
     * @param  string  $name  Cookie name
     *
     */
    public function setName($name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param  string  $name
     * @return Cookie
     */
    public function withName(string $name): Cookie
    {
        $clone = clone $this;
        $clone->name = $name;
        return $clone;
    }

    /**
     * Get the cookie value.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    public function setPort(?string $port): CookieInterface
    {
        $this->port = $port;
        return $this;
    }

    public function setVersion(?int $version): CookieInterface
    {
        $this->version = $version;
        return $this;
    }


    /**
     * @param $value
     * @return $this
     */
    public function setValue(?string $value) : self
    {
        $this->value = $value;
        return $this;
    }

    public function withValue($value): Cookie
    {
        $clone = clone $this;
        $clone->value = $value;
        return $clone;
    }

    /**
     * Get the domain.
     *
     * @return string|null
     */
    public function getDomain(): ?string
    {
        return $this->domain;
    }

    /**
     * @param  string|null  $domain
     * @return $this
     */
    public function setDomain(?string $domain): self
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * @param  string|null  $domain
     * @return Cookie
     */
    public function withDomain(?string $domain): Cookie
    {
        $clone = clone $this;
        $clone->domain = $domain;
        return $clone;
    }

    /**
     * Get the path.
     *
     * @return string
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * @param  string|null  $path
     * @return $this
     */
    public function setPath(?string $path): self
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @param  string|null  $path
     * @return Cookie
     */
    public function withPath(?string $path): Cookie
    {
        $clone = clone $this;
        $clone->path = $path;
        return $clone;
    }

    /**
     * Maximum lifetime of the cookie in seconds.
     *
     * @return int|null
     */
    public function getMaxAge(): int
    {
        return $this->maxAge;
    }

    /**
     * @param  int|null  $maxAge
     * @return $this
     */
    public function setMaxAge(?int $maxAge): self
    {
        $this->maxAge = $maxAge;
        return $this;
    }

    /**
     * @param  int|null  $maxAge
     * @return Cookie
     */
    public function withMaxAge(?int $maxAge): Cookie
    {
        $clone = clone $this;
        $clone->maxAge = $maxAge;
        return $clone;
    }

    /**
     * @return DateTimeInterface
     */
    public function getExpires(): ?DateTimeInterface
    {
        return $this->expires;
    }

    /**
     * @param  DateTimeInterface|null  $expires
     */
    public function setExpires(?DateTimeInterface $expires)
    {
        $this->expires = $expires;
    }

    /**
     * @param  DateTimeInterface|null  $expires
     * @return Cookie
     */
    public function withExpires(?DateTimeInterface $expires) : Cookie
    {
        $clone = clone $this;
        $clone->expires = $expires;
        return $clone;
    }

    /**
     * Get whether or not this is a secure cookie.
     *
     * @return null|bool
     */
    public function isSecure(): bool
    {
        return true === $this->secure;
    }

    /**
     * @param  bool|null  $secure
     * @return $this
     */
    public function setSecure(?bool $secure): self
    {
        $this->secure = $secure;
        return $this;
    }

    public function withSecure(?bool $secure) : Cookie
    {
        $clone = clone $this;
        $clone->secure = $secure;
        return $clone;
    }

    /**
     * @return string|null
     */
    public function getSameSite(): ?string
    {
        return $this->sameSite;
    }

    /**
     * @param  string|null  $sameSite
     * @return $this
     */
    public function setSameSite(?string $sameSite): self
    {
        $this->sameSite = $sameSite;
        return $this;
    }

    /**
     * @param  string|null  $sameSite
     * @return CookieInterface
     */
    public function withSameSite(?string $sameSite): CookieInterface
    {
        $clone = clone $this;
        $clone->sameSite = $sameSite;
        return $clone;
    }

    /**
     * Get whether or not this is a session cookie.
     *
     * @return null|bool
     */
    public function getDiscard(): ?bool
    {
        return $this->discard;
    }

    /**
     * Set whether or not this is a session cookie.
     *
     * @param  bool  $discard  Set to true or false if this is a session cookie
     */
    public function setDiscard(?bool $discard)
    {
        $this->discard = $discard;
    }

    /**
     * @param  bool|null  $discard
     * @return CookieInterface
     */
    public function withDiscard(?bool $discard): CookieInterface
    {
        $clone = clone $this;
        $clone->discard = $discard;
        return $clone;
    }

    /**
     * Get whether or not this is an HTTP only cookie.
     *
     * @return bool
     */
    public function isHttpOnly(): ?bool
    {
        return $this->httpOnly;
    }

    /**
     * Set whether or not this is an HTTP only cookie.
     *
     * @param  bool  $httpOnly  Set to true or false if this is HTTP only
     * @return self
     */
    public function setHttpOnly(?bool $httpOnly): self
    {
        $this->httpOnly = $httpOnly;
        return $this;
    }

    public function withHttpOnly(?bool $httpOnly)
    {
        $clone = clone $this;
        $clone->httpOnly = $httpOnly;
        return $clone;
    }

    /**
     * Check if the cookie matches a path value.
     *
     * A request-path path-matches a given cookie-path if at least one of
     * the following conditions holds:
     *
     * - The cookie-path and the request-path are identical.
     * - The cookie-path is a prefix of the request-path, and the last
     *   character of the cookie-path is %x2F ("/").
     * - The cookie-path is a prefix of the request-path, and the first
     *   character of the request-path that is not included in the cookie-
     *   path is a %x2F ("/") character.
     *
     * @param  string  $requestPath  Path to check against
     *
     * @return bool
     */
    public function matchesPath($requestPath)
    {
        $cookiePath = $this->getPath();

        // Match on exact matches or when path is the default empty "/"
        if ($cookiePath === '/' || $cookiePath == $requestPath) {
            return true;
        }

        // Ensure that the cookie-path is a prefix of the request path.
        if (0 !== strpos($requestPath, $cookiePath)) {
            return false;
        }

        // Match if the last character of the cookie-path is "/"
        if (substr($cookiePath, -1, 1) === '/') {
            return true;
        }

        // Match if the first character not included in cookie path is "/"
        return substr($requestPath, strlen($cookiePath), 1) === '/';
    }

    /**
     * Check if the cookie matches a domain value.
     *
     * @param  string  $domain  Domain to check against
     *
     * @return bool
     */
    public function matchesDomain($domain)
    {
        // Remove the leading '.' as per spec in RFC 6265.
        // http://tools.ietf.org/html/rfc6265#section-5.2.3
        $cookieDomain = ltrim($this->getDomain(), '.');

        // Domain not set or exact match.
        if (!$cookieDomain || !strcasecmp($domain, $cookieDomain)) {
            return true;
        }

        // Matching the subdomain according to RFC 6265.
        // http://tools.ietf.org/html/rfc6265#section-5.1.3
        if (filter_var($domain, FILTER_VALIDATE_IP)) {
            return false;
        }

        return (bool) preg_match('/\.'.preg_quote($cookieDomain).'$/', $domain);
    }

    /**
     * Check if the cookie is expired.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->getExpires() && time() > $this->getExpires();
    }

    /**
     * Check if the cookie is valid according to RFC 6265.
     *
     * @return bool|string Returns true if valid or an error message if invalid
     */
    public function validate()
    {
        // Names must not be empty, but can be 0
        $name = $this->getName();
        if (empty($name) && !is_numeric($name)) {
            return 'The cookie name must not be empty';
        }

        // Check if any of the invalid characters are present in the cookie name
        if (preg_match(
            '/[\x00-\x20\x22\x28-\x29\x2c\x2f\x3a-\x40\x5c\x7b\x7d\x7f]/',
            $name
        )
        ) {
            return 'Cookie name must not contain invalid characters: ASCII '
                .'Control characters (0-31;127), space, tab and the '
                .'following characters: ()<>@,;:\"/?={}';
        }

        // Value must not be empty, but can be 0
        $value = $this->getValue();
        if (empty($value) && !is_numeric($value)) {
            return 'The cookie value must not be empty';
        }

        // Domains must not be empty, but can be 0
        // A "0" is not a valid internet domain, but may be used as server name
        // in a private network.
        $domain = $this->getDomain();
        if (empty($domain) && !is_numeric($domain)) {
            return 'The cookie domain must not be empty';
        }

        return true;
    }

    public function getPort(): ?string
    {
        return $this->port;
    }

    public function getVersion(): ?int
    {
        return $this->version;
    }

    public function toArray(): array
    {
        // TODO: Implement toArray() method.
    }
}
