<?php

namespace Schrapert\DependencyInjection;

interface ContainerInterface
{
    public function instance($abstract, $concrete, $shared = false);

    public function make($abstract, $value);
}
