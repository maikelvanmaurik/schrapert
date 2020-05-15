<?php

namespace Schrapert\DI;

interface ServiceProviderInterface
{
    public function register(ContainerInterface $container);
}
