<?php
namespace Schrapert\Tests\Events\Stubs;

use Schrapert\Events\EventDispatcherInterface;
use Schrapert\Events\SubscriberInterface;

class BasicSubscriber implements SubscriberInterface
{
    public function callback(BasicEvent $event)
    {
        $event->setValue('baz');
    }

    public function subscribe(EventDispatcherInterface $dispatcher)
    {
        $dispatcher->addEventListener(BasicEvent::class, [$this, 'callback']);
    }

    public function unsubscribe(EventDispatcherInterface $dispatcher)
    {
        $dispatcher->removeEventListener(BasicEvent::class, [$this, 'callback']);
    }
}
