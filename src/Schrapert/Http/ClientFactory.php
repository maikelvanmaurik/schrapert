<?php
namespace Schrapert\Http;

use React\Dns\Resolver\Resolver;
use React\EventLoop\LoopInterface;
use React\HttpClient\Factory;
use React\SocketClient\Connector;

class __ClientFactory implements ClientFactoryInterface
{
    private $loop;

    private $dnsResolver;

    private $factory;

    public function __construct(LoopInterface $loop, Resolver $resolver, Factory $factory)
    {
        $this->loop = $loop;
        $this->dnsResolver = $resolver;
        $this->factory = $factory;
    }


    /**
     * @return ClientInterface
     */
    public function factory()
    {
        $connector = new Connector($this->loop, $this->dnsResolver);
        $secureConnector = new Connector($this->loop, $this->dnsResolver);
        return new Client($connector, $secureConnector);
    }
}