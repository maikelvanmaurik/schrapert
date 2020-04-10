<?php

namespace Schrapert\Http\Cookies;

use Countable;
use IteratorAggregate;

interface CookieJarInterface extends Countable, IteratorAggregate
{
    /**
     * @return SetCookie[]
     */
    public function getIterator();

    public function setCookie(SetCookie $cookie);

    public function clear($domain = null, $path = null, $name = null);
}
