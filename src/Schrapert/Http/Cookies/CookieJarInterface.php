<?php

namespace Schrapert\Http\Cookies;

use Countable;
use IteratorAggregate;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;

interface CookieJarInterface extends Countable, IteratorAggregate
{
    public function setCookie(CookieInterface $cookie) : bool;

    public function setCookieIfAllowed(CookieInterface $cookie, RequestInterface $request) : bool;

    public function clear(?string $domain = null, ?string $path = null, ?string $name = null) : void;

    public function extractCookies(ResponseInterface $response, RequestInterface $request);

    public function addCookieHeader(RequestInterface $request) : RequestInterface;

    public function clearSessionCookies() : void;
}
