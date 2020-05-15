<?php

namespace Schrapert\Events;

interface SubscriberInterface
{
    public function subscribe(EventDispatcherInterface $dispatcher);

    public function unsubscribe(EventDispatcherInterface $dispatcher);
}
