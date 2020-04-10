<?php

namespace Schrapert\Feature;

use Schrapert\Event\EventDispatcherInterface;
use Schrapert\Feature\Telnet\TelnetServer;

class TelnetFeature implements FeatureInterface
{
    private $events;

    private $server;

    public function __construct(EventDispatcherInterface $events, TelnetServer $server)
    {
        $this->events = $events;
        $this->server = $server;
    }

    public function init()
    {
        $this->events->addListener('execution-engine-started', [$this, 'startListening']);
        $this->events->addListener('execution-engine-stopped', [$this, 'stopListening']);
    }

    public function startListening()
    {
        $this->server->listen(1337);
    }

    public function protocol()
    {
    }
}
