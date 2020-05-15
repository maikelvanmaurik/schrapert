<?php

namespace Schrapert\Crawling\Events;

use Schrapert\Core\ExecutionEngine;
use Schrapert\Events\Event;

/**
 * Represents an event which is dispatched when the execution engine starts.
 *
 * @package Schrapert\Core\Events
 */
class EngineStartedEvent extends Event
{
    private $engine;

    public function __construct(ExecutionEngine $engine)
    {
        $this->engine = $engine;
    }

    /**
     * Gets the execution engine associated with this event.
     *
     * @return ExecutionEngine
     */
    public function getEngine()
    {
        return $this->engine;
    }
}
