<?php

namespace Schrapert\DI;

use ArrayAccess;

interface ContainerInterface extends ArrayAccess
{
    public function get($service);

    public function bind($service, $value);

    public static function getInstance() : ContainerInterface;

    public static function setInstance(ContainerInterface $container = null) : ContainerInterface;
}
