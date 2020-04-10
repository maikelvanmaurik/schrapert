<?php

namespace Schrapert\Contracts\Events;

use Psr\EventDispatcher\EventDispatcherInterface as PsrDispatcher;
use Psr\EventDispatcher\ListenerProviderInterface as PsrListenerProvider;

interface Dispatcher extends PsrDispatcher, PsrListenerProvider
{
    /**
     * Register an event listener with the dispatcher.
     *
     * @param string $event
     * @param \Closure|string $listener
     * @param int $priority
     * @return self
     */
    public function addEventListener(string $event, $listener, $priority = 0): self;

    /**
     * Determine if a given event has listeners.
     *
     * @param string $event
     * @return bool
     */
    public function hasListeners(string $event): bool;


    public function getListeners(string $event, $priority = null);

    /**
     * Register an event subscriber with the dispatcher.
     *
     * @param Subscriber $subscriber
     * @return self
     */
    public function subscribe(Subscriber $subscriber): self;

    /**
     * @param Subscriber $subscriber
     * @return self
     */
    public function unsubscribe(Subscriber $subscriber): self;

    /**
     * Remove a set of listeners from the dispatcher.
     *
     * @param string $event
     * @param string|callable $listener
     * @return self
     */
    public function removeEventListener(string $event, $listener = null): self;

    public function getEventListenerPriority(string $event, $listener): ?int;
}