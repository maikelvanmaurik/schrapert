<?php
namespace Schrapert\DependencyInjection;

interface ServiceContainerInterface
{
    public function get($service);

    public function set($service, $value);
}