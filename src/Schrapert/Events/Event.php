<?php

namespace Schrapert\Event;

/**
 * Represents an event which occurred during the schrapert lifecycle.
 *
 * This class does not contain event data. It is used by events that do not pass
 * state information to an event handler when an event is raised.
 *
 * Further event handling can be aborted by calling the {@see stopPropagation()}
 */
class Event implements EventInterface
{
    protected $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

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
    public function isPropagationStopped()
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
    public function stopPropagation()
    {
        $this->propagationStopped = true;
    }

    /**
     * Gets the name of the event.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
