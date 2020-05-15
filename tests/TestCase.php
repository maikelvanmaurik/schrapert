<?php
namespace Schrapert\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Schrapert\DI\DefaultContainer;

class TestCase extends BaseTestCase
{
    private $container;

    public function getContainer()
    {
        if (! $this->container) {
            $this->container = new DefaultContainer();
        }
        return $this->container;
    }
}
