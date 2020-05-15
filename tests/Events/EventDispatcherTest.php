<?php

namespace Schrapert\Tests\Events;

use Schrapert\Events\EventDispatcher;
use Schrapert\Tests\Events\Stubs\BasicEvent;
use Schrapert\Tests\Events\Stubs\BasicStoppableEvent;
use Schrapert\Tests\Events\Stubs\BasicSubscriber;
use Schrapert\Tests\TestCase;

class EventDispatcherTest extends TestCase
{
    public function testBasicEventExecution()
    {
        unset($_SERVER['__event.test']);
        $d = new EventDispatcher;
        $d->addEventListener(BasicEvent::class, function (BasicEvent $event) {
            $_SERVER['__event.test'] = 'bar';
        });
        $event = $d->dispatch(new BasicEvent());

        $this->assertInstanceOf(BasicEvent::class, $event);
        $this->assertSame('bar', $_SERVER['__event.test']);
    }

    public function testStoppingEventExecution()
    {
        unset($_SERVER['__event.test']);
        $d = new EventDispatcher;
        $d->addEventListener(BasicStoppableEvent::class, function (BasicStoppableEvent $event) {
            $event->message = 'second message';
            $event->stopPropagation();
            $this->assertTrue(true);
        });
        $d->addEventListener(BasicStoppableEvent::class, function () {
            throw new \Exception('should not be called');
        });

        $event = $d->dispatch(new BasicStoppableEvent('first message'));
        $this->assertSame('second message', $event->message);
    }

    public function testListenersCanBeRemoved()
    {
        unset($_SERVER['__event.test']);
        $d = new EventDispatcher;
        $d->addEventListener(BasicEvent::class, function () {
            $_SERVER['__event.test'] = 'foo';
        });
        $d->removeEventListener(BasicEvent::class);
        $d->dispatch(new BasicEvent());

        $this->assertFalse(isset($_SERVER['__event.test']));
    }

    public function testSpecificListenersCanBeRemoved()
    {
        unset($_SERVER['__event.test']);
        $callback = function () {
            $_SERVER['__event.test'] = 'foo';
        };
        $d = new EventDispatcher;
        $d->addEventListener(BasicEvent::class, function () {
            $_SERVER['__event.test'] = 'baz';
        });
        $d->addEventListener(BasicEvent::class, $callback);
        $d->removeEventListener(BasicEvent::class, $callback);
        $d->dispatch(new BasicEvent());

        $this->assertEquals('baz', $_SERVER['__event.test']);
    }

    public function testListenersCanBeFound()
    {
        $d = new EventDispatcher;
        $this->assertFalse($d->hasEventListeners(BasicEvent::class));

        $d->addEventListener(BasicEvent::class, function () {
            //
        });
        $this->assertTrue($d->hasEventListeners(BasicEvent::class));
    }

    public function testSubscribersCanSubscribe()
    {
        $d = new EventDispatcher;
        $s = new BasicSubscriber();
        $d->subscribe($s);
        $this->assertTrue($d->hasEventListeners(BasicEvent::class));
    }

    /**
     * @depends testSubscribersCanSubscribe
     */
    public function testSubscribersCanUnsubscribed()
    {
        $d = new EventDispatcher;
        $s = new BasicSubscriber;
        $d->subscribe($s);
        $d->unsubscribe($s);
        $this->assertFalse($d->hasEventListeners(BasicEvent::class));
    }
}
