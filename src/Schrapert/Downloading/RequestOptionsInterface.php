<?php
namespace Schrapert\Downloading;

interface RequestOptionsInterface
{
    public function get($key, $default = null);

    public function set($key, $value);

    public function has($key) : bool;
}
