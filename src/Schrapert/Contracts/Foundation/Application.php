<?php
namespace Schrapert\Contracts\Foundation;

use Schrapert\Contracts\DI\Container;

interface Application extends Container
{
    public function register($provider, $options, $force = false);
}