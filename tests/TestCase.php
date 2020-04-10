<?php

namespace Schrapert\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Schrapert\DependencyInjection\DefaultServiceContainer;

class TestCase extends BaseTestCase
{
    private $container;

    public function getContainer()
    {
        if (! $this->container) {
            $this->container = new DefaultServiceContainer();
        }

        return $this->container;
    }
}
