<?php
namespace Schrapert\Tests\DependencyInjection;

use Schrapert\Tests\TestCase;

class ServiceContainerTest extends TestCase
{
    /**
     * @var \Schrapert\DI\Container
     */
    private $container;

    public function setUp(): void
    {
        parent::setUp();
        $this->container = new \Schrapert\DI\Container();
    }

    public function testCanRegisterValues()
    {
        $foo = 'foo';
        $this->container->bind('foo', $foo);
        $this->assertEquals($foo, $this->container->get('foo'));
    }

    public function testCanOverrideValues()
    {
        $this->container->bind('foo', 'bar');
        $this->container->bind('foo', 'baz');
        $this->assertEquals('baz', $this->container->get('foo'));
    }

    public function testObjectsAreRegisteredAsASingleton()
    {
        $this->container->bind('foo', function () {
            return new stdClass();
        });

        $a = $this->container->get('foo');
        $b = $this->container->get('foo');

        $this->assertSame($a, $b);
    }
}
