<?php
namespace Schrapert\Http\Downloader;

use React\Promise\Deferred;
use Schrapert\Http\Downloader\Middleware\DownloadMiddlewareInterface;
use Schrapert\Http\Downloader\Middleware\ProcessRequestMiddlewareInterface;
use Schrapert\Http\Downloader\Middleware\ProcessResponseMiddlewareInterface;
use Schrapert\Http\RequestInterface;
use Schrapert\Log\LoggerInterface;
use InvalidArgumentException;
use React\Promise\PromiseInterface;

class Downloader implements DownloaderInterface
{
    private $queue;

    private $transferring;

    private $logger;

    private $totalConcurrent = 10;

    private $middleware;

    private $transactionFactory;

    public function __construct(LoggerInterface $logger, DownloadTransactionFactoryInterface $transactionFactory, array $middleware = [])
    {
        $this->logger = $logger;
        $this->middleware = [];
        $this->setMiddleware($middleware);
        $this->transactionFactory = $transactionFactory;
        //$this->downloadRequestFactory = $downloadRequestFactory;
        $this->queue = [];
        $this->transferring = [];
    }

    private function removeTransferred(RequestInterface $request)
    {
        $index = array_search($request, $this->transferring, true);
        unset($this->transferring[$index]);
    }

    private function enqueueRequest(RequestInterface $request)
    {
        $this->logger->debug("Enqueue download request %s", [$request->getUri()]);
        $deferred = new Deferred();

        $this->queue[] = [$request, $deferred];

        $this->processRequest();

        return $deferred->promise();
    }

    private function processRequest()
    {
        while(count($this->queue) > 0 && count($this->transferring) < $this->totalConcurrent) {
            list($request,$deferred) = array_pop($this->queue);
            $this->logger->debug("Process enqueued download request %s", [$request->getUri()]);
            $download = $this->fetch($request, $deferred);
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

    private function fetch(RequestInterface $request, Deferred $deferred)
    {
        /*
        $this->logger->debug("Download {uri}", ['uri' => (string)$request->getUri()]);
        $this->transferring[] = $request;
        /*
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
        */

        $transaction = $this
            ->transactionFactory
            ->createTransaction($request)
            ->withOptions($request->getMetaData());

        return $transaction->send($request);
    }

    public function withMiddleware(DownloadMiddlewareInterface $middleware)
    {
        $new = clone $this;
        $new->middleware[] = $middleware;
        return $new;
    }

    public function withoutMiddleware(DownloadMiddlewareInterface $middleware)
    {
        $new = clone $this;
        $new->middleware = [];
        foreach($this->middleware as $item) {
            if($item !== $middleware) {
                $new->middleware[] = $item;
            }
        }

    }

    public function hasMiddleware(DownloadMiddlewareInterface $middleware)
    {
        foreach($this->middleware as $item) {
            if($middleware === $item) {
                return true;
            }
        }
        return false;
    }

    public function getMiddleware()
    {
        return $this->middleware;
    }

    private function setMiddleware($middleware)
    {
        foreach((array)$middleware as $item) {
            if(!$item instanceof DownloadMiddlewareInterface) {
                throw new InvalidArgumentException("Invalid middleware");
            }
        }
        $this->middleware = $middleware;
    }

    public function download(RequestInterface $request)
    {
        $this->logger->debug("Fetch %s", [$request->getUri()]);

        /*
         * Create 2 deferred objects 1 for creating the request object and 1 to pass back to
         * the downloader
         */
        $downloaded = new Deferred();
        $deferred = new Deferred();
        $promise = $deferred->promise();

        foreach ($this->middleware as $middleware) {
            if(!$middleware instanceof ProcessRequestMiddlewareInterface) {
                continue;
            }
            $promise = $promise->then(function ($request) use ($middleware, &$promise, &$downloaded) {
                $result = $middleware->processRequest($request);
                if($result instanceof PromiseInterface) {
                    return $result->otherwise(function($e) use ($downloaded) {
                        $downloaded->reject($e);
                        throw $e;
                    });
                }
                return $result;
            });
        }


        // When all the middleware have run call the download method
        $promise = $promise->then(function ($request) {
            $this->logger->debug("Invoke request %s", [$request->getUri()]);
            // Here the request will be processed by all middleware
            return $this->enqueueRequest($request);
        });

        // With the downloaded response apply the post processing middleware
        foreach($this->middleware as $middleware) {
            if(!$middleware instanceof ProcessResponseMiddlewareInterface) {
                continue;
            }
            $promise = $promise->then(function($response) use ($middleware, $request) {
                $this->logger->debug("Middleware manager: let the %s middleware process the response", [get_class($middleware)]);
                return $middleware->processResponse($response, $request);
            });
        }

        $promise->then(function ($response) use (&$downloaded, $request) {
            $this->logger->debug("Middleware manager: downloaded %s", [$request->getUri()]);
            $downloaded->resolve($response);
            return $response;
        });

        // Start creating the request object
        $deferred->resolve($request);

        return $downloaded->promise();
    }

    public function needsBackOut()
    {
        return count($this->transferring) >= $this->totalConcurrent;
    }
}