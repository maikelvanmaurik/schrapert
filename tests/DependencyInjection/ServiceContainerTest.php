<?php
namespace Schrapert\Tests\DependencyInjection;

use Schrapert\Tests\TestCase;

class ServiceContainerTest extends TestCase
{
    /**
     * @var \Schrapert\DependencyInjection\ServiceContainer
     */
    private $container;

    public function setUp(): void
    {
        parent::setUp();
        $this->container = new \Schrapert\DependencyInjection\ServiceContainer();
    }

    public function testCanRegisterValues()
    {
        $foo = 'foo';
        $this->container->set('foo', $foo);
        $this->assertEquals($foo, $this->container->get('foo'));
    }

    public function testCanOverrideValues()
    {
        $this->container->set('foo', 'bar');
        $this->container->set('foo', 'baz');
        $this->assertEquals('baz', $this->container->get('foo'));
    }

    public function testObjectsAreRegisteredAsASingleton()
    {
        $this->container->set('foo', function() {
            return new stdClass();
        });

        $a = $this->container->get('foo');
        $b = $this->container->get('foo');

        $this->assertSame($a, $b);
    }
}