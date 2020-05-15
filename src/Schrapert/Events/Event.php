<?php

namespace Schrapert\Events;

use Psr\EventDispatcher\StoppableEventInterface;

/**
 * Represents an event which occurred during the Schrapert lifecycle.
 *
 * This class does not contain event data. It is used by events that do not pass
 * state information to an event handler when an event is raised.
 *
 * Further event handling can be aborted by calling the {@see stopPropagation()}
 * if you extend this class you should implement the
 * Psr\EventDispatcher\StoppableEventInterface as a marker interface to indicate
 * the event should be stoppable.
 */
class Event
{
    /**
     * @var bool Whether no further event listeners should be triggered
     */
    private $propagationStopped = false;

    /**
     * Returns whether further event listeners should be triggered.
     *
     * @see Event::stopPropagation()
     *
     * @return bool Whether propagation was already stopped for this event
     */
    public function isPropagationStopped() : bool
    {
        return $this->propagationStopped;
    }

    /**
     * Stops the propagation of the event to further event listeners.
     *
     * If multiple event listeners are connected to the same event, no
     * further event listener will be triggered once any trigger calls
     * stopPropagation().
     */
    public function stopPropagation() : self
    {
        if (! $this->isStoppable()) {
            throw new \RuntimeException('Events is not stoppable');
        }
        $this->propagationStopped = true;
        return $this;
    }

    /**
     * @return bool
     */
    public function isStoppable() : bool
    {
        return $this instanceof StoppableEventInterface;
    }
}
