<?php
namespace Schrapert\Http\Cookies;

interface CookieJarManagerInterface extends \ArrayAccess
{
    public function get(string $name) : CookieJarInterface;

    public function set(string $name, CookieJarInterface $jar) : self;

    public function remove(string $name) : self;
}
