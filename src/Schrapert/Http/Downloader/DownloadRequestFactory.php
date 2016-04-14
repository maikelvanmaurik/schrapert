<?php
namespace Schrapert\Http\Downloader;

use React\SocketClient\Connector;
use React\SocketClient\SecureConnector;
use Schrapert\Http\RequestInterface;

class DownloadRequestFactory
{
    private $connector;

    private $secureConnector;

    public function __construct(Connector $connector, SecureConnector $secureConnector)
    {
        $this->connector = $connector;
        $this->secureConnector = $secureConnector;
    }

    private function getConnectorForScheme($scheme)
    {
        return ('https' === $scheme) ? $this->secureConnector : $this->connector;
    }

    public function factory(RequestInterface $request)
    {
        $connector = $this->getConnectorForScheme($request->getProtocol());
        return new DownloadRequest($request, $connector);
    }
}