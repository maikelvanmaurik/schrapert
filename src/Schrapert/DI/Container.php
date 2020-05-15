<?php

namespace Schrapert\DI;

use Closure;

class Container implements ContainerInterface
{
    private array $bindings;

    private array $with;

    private array $instances;

    private static ContainerInterface $instance;

    public function __construct()
    {
        $this->bindings = [];
        $this->instances = [];
        $this->with = [];
    }


    /**
     * Get the globally available instance of the container.
     *
     * @return ContainerInterface
     */
    public static function getInstance() : ContainerInterface
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    /**
     * Set the shared instance of the container.
     *
     * @param ContainerInterface|null $container
     * @return ContainerInterface
     */
    public static function setInstance(ContainerInterface $container = null) : ContainerInterface
    {
        return static::$instance = $container;
    }

    public function register(ServiceProviderInterface $provider)
    {
        $provider->register($this);
        return $this;
    }

    private function resolve($abstract, $parameters = [])
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        if (! isset($this->bindings[$abstract])) {
            return null;
        }

        $concrete = $this->bindings[$abstract]['concrete'];

        $this->with[] = $parameters;

        $object = $this->build($concrete);

        if ($this->isShared($abstract)) {
            $this->instances[$abstract] = $object;
        }

        array_pop($this->with);

        return $object;
    }

    public function isShared($abstract)
    {
        return isset($this->instances[$abstract]) ||
            (isset($this->bindings[$abstract]['shared']) &&
                $this->bindings[$abstract]['shared'] === true);
    }

    public function build($concrete)
    {
        if ($concrete instanceof Closure) {
            return $concrete($this, $this->getLastParameterOverride());
        }
    }

    /**
     * Get the last parameter override.
     *
     * @return array
     */
    protected function getLastParameterOverride()
    {
        return count($this->with) ? end($this->with) : [];
    }

    public function get($abstract)
    {
        return $this->resolve($abstract);
    }

    public function bind($abstract, $concrete = null, $shared = false) : ContainerInterface
    {
        if (null === $concrete) {
            $concrete = $abstract;
        }
        $this->bindings[$abstract] = compact('concrete', 'shared');
        return $this;
    }

    public function singleton($abstract, $concrete = null) : ContainerInterface
    {
        $this->bind($abstract, $concrete, true);
        return $this;
    }

    /**
     * Determine if a given offset exists.
     *
     * @param  string  $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->bound($key);
    }

    /**
     * Get the value at a given offset.
     *
     * @param  string  $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Set the value at a given offset.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->bind($key, $value instanceof Closure ? $value : function () use ($value) {
            return $value;
        });
    }

    /**
     * Unset the value at a given offset.
     *
     * @param  string  $key
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($this->bindings[$key], $this->instances[$key], $this->resolved[$key]);
    }
}
