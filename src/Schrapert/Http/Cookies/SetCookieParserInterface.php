<?php

namespace Schrapert\Http\Cookies;

interface SetCookieParserInterface
{
    /**
     * @param $str
     * @return SetCookie
     */
    public function parse($str);
}
