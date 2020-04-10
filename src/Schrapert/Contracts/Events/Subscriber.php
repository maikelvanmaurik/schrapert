<?php
namespace Schrapert\Contracts\Events;

interface Subscriber
{
    public function subscribe(Dispatcher $dispatcher);

    public function unsubscribe(Dispatcher $dispatcher);
}