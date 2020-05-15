<?php
namespace Schrapert\Http\Cookies;

interface CookieJarFactoryInterface
{
    public function createCookieJar() : CookieJarInterface;
}
