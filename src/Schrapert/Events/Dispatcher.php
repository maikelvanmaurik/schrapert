<?php
declare(strict_types=1);

namespace Schrapert\Events;

use Psr\EventDispatcher\StoppableEventInterface;
use RuntimeException;
use Schrapert\Contracts\Events\Dispatcher as BaseDispatcher;
use Schrapert\Contracts\Events\Event;
use Schrapert\Contracts\Events\Subscriber;

/**
 * The event dispatcher allows event listeners to be registered and is
 * responsible for dispatching the events.
 *
 * @package Schrapert\Events
 */
class Dispatcher implements BaseDispatcher
{
    private $listeners = array();
    private $sorted = array();

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
    public function getListenersForEvent(object $event): iterable
    {
        $event = get_class($event);
        if (null !== $event) {
            if (empty($this->listeners[$event])) {
                return [];
            }

            if (! isset($this->sorted[$event])) {
                $this->sortListeners($event);
            }

            return $this->sorted[$event];
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
    public function getEventListenerPriority(string $event, $listener): ?int
    {
        if (!isset($this->listeners[$event])) {
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
    public function hasListeners(string $event)
    {
        return (bool)iterator_count($this->getListenersForEvent($event));
    }

    /**
     * {@inheritdoc}
     */
    public function addEventListener(string $event, $listener, $priority = 0)
    {
        $this->listeners[$event][$priority][] = $listener;
        unset($this->sorted[$event]);
    }

    /**
     * {@inheritdoc}
     */
    public function removeEventListener(string $event, $listener = null)
    {
        if (!isset($this->listeners[$event])) {
            return $this;
        }
        if (null === $listener) {
            $this->listeners[$event] = [];
            unset($this->sorted[$event]);
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
    public function subscribe(Subscriber $subscriber)
    {
        $subscriber->subscribe($this);
    }

    /**
     * {@inheritdoc}
     */
    public function unsubscribe(Subscriber $subscriber)
    {
        $subscriber->unsubscribe($this);
    }

    /**
     * Sorts the internal list of listeners for the given event by priority.
     *
     * @param string $eventName The name of the event
     */
    private function sortListeners($eventName)
    {
        krsort($this->listeners[$eventName]);
        $this->sorted[$eventName] = call_user_func_array('array_merge', $this->listeners[$eventName]);
    }
}