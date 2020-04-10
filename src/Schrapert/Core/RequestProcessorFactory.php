<?php

namespace Schrapert\Core;

use RuntimeException;

class RequestProcessorFactory implements RequestProcessorFactoryInterface
{
    private $types;

    public function __construct()
    {
        $this->types = [];
    }

    public function register($type, callable $fn)
    {
        $this->types[$type] = $fn;
    }

    public function factory($type)
    {
        if (is_object($type)) {
            $type = get_class($type);
        }

        if (! isset($this->types[$type])) {
            throw new RuntimeException(sprintf("Request processor not registered for type '%s'", (string) $type));
        }

        return call_user_func($this->types[$type]);
    }
}
