<?php
namespace Schrapert\Http\Downloader;

use React\SocketClient\ConnectorInterface;
use Schrapert\Http\RequestInterface;

class DownloadRequestFactory
{
    private $connector;

    private $secureConnector;

    public function __construct(ConnectorInterface $connector, ConnectorInterface $secureConnector)
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
        $connector = $this->getConnectorForScheme($request->getUri()->getScheme());
        return new DownloadRequest($request, $connector);
    }
}