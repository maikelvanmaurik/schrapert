<?php
namespace Schrapert\Feature\Telnet;

use React\EventLoop\LoopInterface;
use React\Socket\Server;

class TelnetServer extends Server
{
    public function __construct(LoopInterface $loop)
    {
        parent::__construct($loop);
        $this->on('connection', array($this, 'onConnection'));
    }

    public function onConnection($connection)
    {

    }
}