<?php
namespace Schrapert\Core\Event;

use Schrapert\Core\ExecutionEngine;
use Schrapert\Event\Event;

/**
 * Represents an event which is dispatched when the execution engine starts.
 *
 * @package Schrapert\Core\Event
 */
class EngineStartedEvent extends Event
{
    private $engine;

    public function __construct(ExecutionEngine $engine)
    {
        parent::__construct('execution-engine-started');
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