<?php
namespace Schrapert\Http\Cookies;

class CookieJarFactory implements CookieJarFactoryInterface
{
    public function createCookieJar() : CookieJarInterface
    {
        return new CookieJar(new Rfc2616CookiePolicy());
    }
}
