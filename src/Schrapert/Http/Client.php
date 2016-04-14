<?php
namespace Schrapert\Http;

use React\SocketClient\ConnectorInterface;

class Client
{
    private $connector;
    private $secureConnector;
    public function __construct(ConnectorInterface $connector, ConnectorInterface $secureConnector)
    {
        $this->connector = $connector;
        $this->secureConnector = $secureConnector;
    }
    public function request($method, $uri, array $headers = [], $protocolVersion = '1.0')
    {
        $requestData = new RequestData($method, $uri, $headers, $protocolVersion);
        $connector = $this->getConnectorForScheme($requestData->getScheme());
        return new Request($connector, $requestData);
    }
    private function getConnectorForScheme($scheme)
    {
        return ('https' === $scheme) ? $this->secureConnector : $this->connector;
    }
}