<?php
namespace Schrapert\Tests\Events;

use Psr\EventDispatcher\StoppableEventInterface;
use Schrapert\Contracts\Events\Subscriber;
use Schrapert\Events\Dispatcher;
use Schrapert\Events\PropagationStoppable;
use Schrapert\Tests\TestCase;

class EventDispatcherTest extends TestCase
{
    public function testBasicEventExecution()
    {
        unset($_SERVER['__event.test']);
        $d = new Dispatcher;
        $d->addEventListener(BasicEvent::class, function(BasicEvent $event) {
            $_SERVER['__event.test'] = 'bar';
        });
        $event = $d->dispatch(new BasicEvent());

        $this->assertInstanceOf(BasicEvent::class, $event);
        $this->assertSame('bar', $_SERVER['__event.test']);
    }

    public function testStoppingEventExecution()
    {
        unset($_SERVER['__event.test']);
        $d = new Dispatcher;
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
        $d = new Dispatcher;
        $d->addEventListener( BasicEvent::class, function () {
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
        $d = new Dispatcher;
        $d->addEventListener(BasicEvent::class, function() {
            $_SERVER['__event.test'] = 'baz';
        });
        $d->addEventListener( BasicEvent::class, $callback);
        $d->removeEventListener(BasicEvent::class, $callback);
        $d->dispatch(new BasicEvent());

        $this->assertEquals('baz', $_SERVER['__event.test']);
    }

    public function testListenersCanBeFound()
    {
        $d = new Dispatcher;
        $this->assertFalse($d->hasListeners(BasicEvent::class));

        $d->addEventListener(BasicEvent::class, function () {
            //
        });
        $this->assertTrue($d->hasListeners(BasicEvent::class));
    }

    public function testSubscribersCanSubscribe()
    {
        $d = new Dispatcher;
        $s = new BasicSubscriber;
        $d->subscribe($s);
        $this->assertTrue($d->hasListeners());
    }

}

class BasicEvent
{
    private $value;

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }
}

class BasicStoppableEvent implements StoppableEventInterface
{
    use PropagationStoppable;

    public $message;

    public function __construct($message)
    {
        $this->message = $message;
    }
}

class BasicSubscriber implements Subscriber
{
    public function callback(BasicEvent $event)
    {
        $event->setValue('baz');
    }

    public function subscribe(\Schrapert\Contracts\Events\Dispatcher $dispatcher)
    {
        $dispatcher->addEventListener(BasicEvent::class, [$this, 'callback']);
    }

    public function unsubscribe(\Schrapert\Contracts\Events\Dispatcher $dispatcher)
    {
        $dispatcher->removeEventListener(BasicEvent::class, [$this, 'callback']);
    }

}