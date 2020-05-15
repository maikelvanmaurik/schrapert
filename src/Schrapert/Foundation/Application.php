<?php

namespace Schrapert\Foundation;

use Schrapert\DI\Container;
use Schrapert\DI\ServiceProviderInterface;

class Application extends Container
{
    protected $providers = [];

    public function bootstrapWith(array $bootstrappers)
    {
    }

    public function register(ServiceProviderInterface $provider)
    {
        $this->providers[] = $provider;
        parent::register($provider);
        return $this;
    }

    public function boot()
    {
        if ($this->isBooted()) {
            return;
        }
    }
}
