<?php
namespace Schrapert\Event;

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

    public function getName()
    {
        return $this->name;
    }
}