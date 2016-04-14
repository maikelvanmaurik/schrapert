<?php
namespace Schrapert\Http\Downloader;

use React\Promise\Deferred;
use Schrapert\Http\RequestInterface;
use Schrapert\Http\ResponseInterface;
use Schrapert\Http\ClientFactoryInterface;
use Schrapert\Log\LoggerInterface;
use Schrapert\SpiderInterface;

class Downloader implements DownloaderInterface
{
    private $active;

    private $logger;

    private $totalConcurrent = 10;

    public function __construct(LoggerInterface $logger, ClientFactoryInterface $clientFactory, DownloadRequestFactory $downloadRequestFactory)
    {
        $this->logger = $logger;
        $this->clientFactory = $clientFactory;
        $this->downloadRequestFactory = $downloadRequestFactory;
        $this->active = [];
    }

    public function fetch(RequestInterface $request, SpiderInterface $spider)
    {
        $deferred = new Deferred();

        $downloadRequest = $this->downloadRequestFactory->factory($request);

        $this->logger->debug("Start download %s", [$downloadRequest->getUri()]);

        $downloadRequest->on('response', function($response) use ($deferred, $request) {
            $deferred->resolve($response);
        });


        $downloadRequest->end();

        return $deferred->promise();
    }

    public function processResponse(RequestInterface $request, ResponseInterface $response, SpiderInterface $spider)
    {
        // TODO: Implement processResponse() method.
    }

    public function processRequest(RequestInterface $request, SpiderInterface $spider)
    {
        // TODO: Implement processRequest() method.
    }

    public function needsBackOut()
    {
        return count($this->active) >= $this->totalConcurrent;
    }
}