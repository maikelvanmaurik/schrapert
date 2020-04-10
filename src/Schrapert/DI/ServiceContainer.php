<?php
namespace Schrapert\DependencyInjection;

class ServiceContainer implements ServiceContainerInterface
{
    private $registered;

    private $resolved;

    public function __construct()
    {
        $this->registered = [];
        $this->resolved = [];
    }

    private function resolve($name, $value)
    {
        if(array_key_exists($name, $this->resolved)) {
            return $this->resolved[$name];
        }
        if(is_callable($value)) {
            $this->resolved[$name] = call_user_func($value);
        } else {
            $this->resolved[$name] = $value;
        }
        return $this->resolved[$name];
    }

    public function get($name)
    {
        if(!array_key_exists($name, $this->registered)) {
            return null;
        }
        $value = $this->registered[$name];
        return $this->resolve($name, $value);
    }

    public function set($name, $value)
    {
        $this->registered[$name] = $value;
    }
}