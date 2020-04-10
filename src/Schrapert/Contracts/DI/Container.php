<?php

namespace Schrapert\Contracts\DI;

use Closure;

interface Container
{
    public function bound($abstract);

    public function bind($abstract, $concrete = null, $shared = false);

    public function alias($abstract, $alias);

    /**
     * Register a binding if it hasn't already been registered.
     *
     * @param string $abstract
     * @param Closure|string|null $concrete
     * @param bool $shared
     * @return void
     */
    public function bindIf($abstract, $concrete = null, $shared = false);

    /**
     * Register a shared binding in the container.
     *
     * @param string|array $abstract
     * @param Closure|string|null $concrete
     * @return void
     */
    public function singleton($abstract, $concrete = null);

    /**
     * Call the given closure or method and inject its dependencies.
     *
     * @param callable|string $function
     * @param array $parameters
     * @param string|null $defaultMethod
     * @return mixed
     */
    public function call($function, array $parameters = [], $defaultMethod = null);

    /**
     * Determine if the given abstract type has been resolved.
     *
     * @param string $abstract
     * @return bool
     */
    public function resolved($abstract);

    /**
     * Register an existing instance as shared in the container.
     *
     * @param string $abstract
     * @param mixed $instance
     * @return void
     */
    public function instance($abstract, $instance);

    /**
     * Resolve the given type from the container.
     *
     * @param string $abstract
     * @param array $parameters
     * @return mixed
     */
    public function make($abstract, array $parameters = []);

    /**
     * Register a new resolving callback.
     *
     * @param string $abstract
     * @param Closure|null $callback
     * @return void
     */
    public function resolving($abstract, Closure $callback = null);

    /**
     * Register a new after resolving callback.
     *
     * @param string $abstract
     * @param Closure|null $callback
     * @return void
     */
    public function afterResolving($abstract, Closure $callback = null);
}