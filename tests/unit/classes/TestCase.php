<?php
namespace Schrapert\Test\Unit;

use PHPUnit_Framework_TestCase;
use Schrapert\DependencyInjection\DefaultServiceContainer;

class TestCase extends PHPUnit_Framework_TestCase
{
    private $container;

    public function getContainer()
    {
        if(!$this->container) {
            $this->container = new DefaultServiceContainer();
        }
        return $this->container;
    }
}