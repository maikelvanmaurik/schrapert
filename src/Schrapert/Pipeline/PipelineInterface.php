<?php

namespace Schrapert\Pipeline;

use React\Promise\PromiseInterface;

interface PipelineInterface
{
    public function __invoke();
}
