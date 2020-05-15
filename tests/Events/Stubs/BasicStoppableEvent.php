<?php
namespace Schrapert\Tests\Events\Stubs;

use Schrapert\Events\Event;
use Psr\EventDispatcher\StoppableEventInterface;

class BasicStoppableEvent extends Event implements StoppableEventInterface
{
    public $message;

    public function __construct($message)
    {
        $this->message = $message;
    }
}
