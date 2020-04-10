<?php

namespace Schrapert\Core;

interface RequestProcessorFactoryInterface
{
    /**
     * @param mixed $type
     * @param callable $fn
     * @return void
     */
    public function register($type, callable $fn);

    /**
     * @param mixed $type
     * @return RequestProcessorInterface
     */
    public function factory($type);
}
