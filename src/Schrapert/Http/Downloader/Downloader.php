<?php
namespace Schrapert\Http\Downloader;

use React\Promise\Deferred;
use Schrapert\Http\Downloader\Middleware\DownloadMiddlewareManager;
use Schrapert\Http\RequestInterface;
use Schrapert\Log\LoggerInterface;
use Schrapert\SpiderInterface;

class Downloader implements DownloaderInterface
{
    private $queue;

    private $transferring;

    private $logger;

    private $totalConcurrent = 10;

    public function __construct(LoggerInterface $logger, DownloadMiddlewareManager $middleware,  DownloadRequestFactory $downloadRequestFactory)
    {
        $this->logger = $logger;
        $this->middleware = $middleware;
        $this->downloadRequestFactory = $downloadRequestFactory;
        $this->queue = [];
        $this->transferring = [];
    }

    private function removeTransferred(RequestInterface $request)
    {
        $index = array_search($request, $this->transferring, true);
        unset($this->transferring[$index]);
    }

    private function enqueueRequest(RequestInterface $request, SpiderInterface $spider)
    {
        $this->logger->debug("Enqueue download request %s", [$request->getUri()]);
        $deferred = new Deferred();

        $this->queue[] = [$request, $deferred];

        $this->processRequest($spider);

        return $deferred->promise();
    }

    private function processRequest(SpiderInterface $spider)
    {
        while(count($this->queue) > 0 && count($this->transferring) < $this->totalConcurrent) {
            list($request,$deferred) = array_pop($this->queue);
            $this->logger->debug("Process enqueued download request %s", [$request->getUri()]);
            $download = $this->download($request, $deferred);
            $download->then(function($response) use ($deferred) {
                $deferred->resolve($response);
                return $response;
            }, function($error) use ($deferred) {
                $deferred->reject($error);
            });
            $download->always(function() use ($request) {
               $this->removeTransferred($request);
            });
        }
    }

    private function download(RequestInterface $request, Deferred $deferred)
    {
        $this->logger->debug("Download %s", [$request->getUri()]);
        $this->transferring[] = $request;
        $downloadRequest = $this->downloadRequestFactory->factory($request);
        $downloadRequest->on('response', function($response) use ($deferred, $request) {
            $this->logger->debug("Got download response for %s", [$request->getUri()]);
            $deferred->resolve($response);
        });

        $downloadRequest->on('end', function() use ($request) {
            $this->logger->debug("End download request %s", [$request->getUri()]);
        });

        $downloadRequest->on('error', function($e) use ($deferred) {
            $deferred->reject($e);
            $this->logger->debug("Download error");
        });

        $downloadRequest->end();

        return $deferred->promise();
    }

    public function fetch(RequestInterface $request, SpiderInterface $spider)
    {
        $this->logger->debug("Fetch %s", [$request->getUri()]);
        return $this->middleware->download(function() use ($request, $spider) {
            return $this->enqueueRequest($request, $spider);
        }, $request, $spider);
    }

    public function needsBackOut()
    {
        return count($this->transferring) >= $this->totalConcurrent;
    }
}