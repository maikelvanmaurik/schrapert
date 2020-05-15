<?php
namespace Schrapert\Http\Cookies;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface CookiePolicyInterface
{
    public function extractCookies(ResponseInterface $response, RequestInterface $request, CookieJarInterface $jar);

    public function setAllowed(CookieInterface $cookie, RequestInterface $request) : bool;

    public function returnAllowed(CookieInterface $cookie, RequestInterface $request) : bool;

    public function cookiesForRequest(iterable $cookies, RequestInterface $request) : iterable;
}