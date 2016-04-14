<?php
namespace Schrapert\Core;

use React\Promise\PromiseInterface;

interface RequestProcessInterface
{
    public function needsBackOut();

    /**
     * @return PromiseInterface
     */
    public function run();
}