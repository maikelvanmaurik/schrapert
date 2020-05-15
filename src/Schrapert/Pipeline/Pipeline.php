<?php

namespace Schrapert\Pipeline;

class Pipeline implements PipelineInterface
{
    /**
     * The array of class pipes.
     *
     * @var array
     */
    protected $pipes = [];

    public function __construct($pipes)
    {
        $this->pipes = is_array($pipes)
            ? $pipes
            : func_get_args();
    }

    private function resolve(callable $callback): callable
    {
        $prev = $callback;
        foreach (\array_reverse($this->pipes) as $fn) {
            $prev = $fn($prev);
        }
        return $prev;
    }

    public function run(callable $callback, ...$params)
    {
        return $this->resolve($callback)(...$params);
    }

    public function runAndReturn(...$params)
    {
        return $this->resolve(function ($passable) {
            return $passable;
        })(...$params);
    }

    public function __invoke()
    {
        return $this->runAndReturn(...func_get_args());
    }
}
