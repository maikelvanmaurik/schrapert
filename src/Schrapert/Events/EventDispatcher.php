<?php

namespace Schrapert\Events;

use Psr\EventDispatcher\StoppableEventInterface;

/**
 * The event dispatcher allows event listeners to be registered and is
 * responsible for dispatching the events.
 */
class EventDispatcher implements EventDispatcherInterface
{
    private $listeners = [];
    private $sorted = [];

    /**
     * {@inheritdoc}
     */
    public function dispatch(object $event)
    {
        $listeners = $this->getListenersForEvent($event);

        $stoppable = $event instanceof StoppableEventInterface;
        foreach ($listeners as $listener) {
            if ($stoppable && $event->isPropagationStopped()) {
                break;
            }
            $listener($event);
        }

        return $event;
    }

    /**
     * {@inheritdoc}
     */
    public function getListenersForEvent(object $event) : iterable
    {
        if (null !== $event) {
            $event = get_class($event);
            return $this->getEventListeners($event);
        }

        foreach ($this->listeners as $eventName => $eventListeners) {
            if (! isset($this->sorted[$eventName])) {
                $this->sortListeners($eventName);
            }
        }

        return array_filter($this->sorted);
    }

    /**
     * {@inheritdoc}
     */
    public function getEventListeners(string $event = null, $priority = null) : iterable
    {
        if (null === $event || empty($this->listeners[$event])) {
            return [];
        }

        if (! isset($this->sorted[$event])) {
            $this->sortListeners($event);
        }

        return null === $priority
             ? $this->sorted[$event]
             : ($this->listeners[$event][$priority] ?? []);
    }

    /**
     * {@inheritdoc}
     */
    public function getEventListenerPriority(string $event, $listener) : ?int
    {
        if (! isset($this->listeners[$event])) {
            return null;
        }

        foreach ($this->listeners[$event] as $priority => $listeners) {
            if (false !== in_array($listener, $listeners, true)) {
                return $priority;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function hasEventListeners(string $event) : bool
    {
        return (bool) count($this->getEventListeners($event));
    }

    /**
     * {@inheritdoc}
     */
    public function addEventListener(string $event, $listener, $priority = 0) : self
    {
        $this->listeners[$event][$priority][] = $listener;
        $this->clearSortedListeners($event);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeEventListener(string $event, $listener = null) : self
    {
        if (! isset($this->listeners[$event])) {
            return $this;
        }
        if (null === $listener) {
            $this->listeners[$event] = [];
            $this->clearSortedListeners($event);
            return $this;
        }
        foreach ($this->listeners[$event] as $priority => $listeners) {
            if (false !== ($key = array_search($listener, $listeners, true))) {
                unset($this->listeners[$event][$priority][$key], $this->sorted[$event]);
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function subscribe(SubscriberInterface $subscriber) : self
    {
        $subscriber->subscribe($this);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function unsubscribe(SubscriberInterface $subscriber) : self
    {
        $subscriber->unsubscribe($this);
        return $this;
    }

    private function clearSortedListeners(string $event = null)
    {
        if (null === $event) {
            $this->sorted = [];
        } else {
            unset($this->sorted[$event]);
        }
    }

    /**
     * Sorts the internal list of listeners for the given event by priority.
     *
     * @param string $event The name of the event
     */
    private function sortListeners(string $event)
    {
        krsort($this->listeners[$event]);
        $this->sorted[$event] = call_user_func_array('array_merge', $this->listeners[$event]);
    }
}
