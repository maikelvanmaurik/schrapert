<?php

namespace Schrapert\Http\Cookies;

interface CookieStringParserInterface
{
    /**
     * @param string $string
     * @param CookieInterface $cookie
     */
    public function parse(string $string, CookieInterface $cookie) : void;
}
