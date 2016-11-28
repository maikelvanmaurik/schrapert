<?php
namespace Schrapert\Http\Downloader;

use Psr\Http\Message\ResponseInterface;
use React\Promise\Deferred;
use Schrapert\Event\EventDispatcherInterface;
use Schrapert\Http\Downloader\Event\DownloadCompleteEvent;
use Schrapert\Http\Downloader\Event\DownloadRequestEvent;
use Schrapert\Http\Downloader\Event\ResponseDownloadedEvent;
use Schrapert\Http\Downloader\Exception\DownloaderTimeoutException;
use Schrapert\Http\Downloader\Exception\TransactionTimeoutException;
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

    private $events;

    public function __construct(EventDispatcherInterface $events, LoggerInterface $logger, DownloadTransactionFactoryInterface $transactionFactory, array $middleware = [])
    {
        $this->events = $events;
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
        $this->logger->debug("Enqueue download request {uri}", ['uri' => $request->getUri()]);
        $deferred = new Deferred();

        $this->queue[] = [$request, $deferred];

        $this->processRequest();

        return $deferred->promise();
    }

    private function processRequest()
    {
        while(count($this->queue) > 0 && count($this->transferring) < $this->totalConcurrent) {
            list($request,$deferred) = array_shift($this->queue);
            $this->logger->debug("Process en-queued download request {uri}", ['uri' => (string)$request->getUri()]);
            $download = $this->fetch($request);
            $download->then(function($response) use ($deferred, $request) {
                if($response instanceof \Schrapert\Http\ResponseInterface) {
                    $response = $response->withMetaData('request', $request);
                }
                $deferred->resolve($response);
                return $response;
            }, function($error) use ($deferred) {
                $deferred->reject($error);
                return $error;
            });
            $download->always(function() use ($request) {
               $this->removeTransferred($request);
            });
        }
    }

    private function fetch(RequestInterface $request)
    {
        $this->transferring[] = $request;

        $this->events->dispatch(new DownloadRequestEvent($request, date_create()));

        $transaction = $this
            ->transactionFactory
            ->createTransaction($request)
            ->withOptions($request->getMetaData());

        return $transaction->send($request)->then(function(ResponseInterface $response) {
            return $response;
        }, function(\Exception $e) use ($request) {
            if($e instanceof TransactionTimeoutException) {
                $e = new DownloaderTimeoutException($this, $request);
            }
            throw $e;
        });
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
        $this->logger->debug("Downloader: download {uri}", ['uri' => $request->getUri()]);

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
                if($result instanceof ResponseInterface) {
                    $downloaded->resolve($result);
                }
                return $result;
            });
        }


        // When all the middleware have run call the download method
        $promise = $promise->then(function ($message) {
            if($message instanceof RequestInterface) {
                $this->logger->debug("Downloader: invoke request {uri}", ['uri' => $message->getUri()]);
                // Here the request will be processed by all middleware
                return $this->enqueueRequest($message);
            }
            return $message;
        });

        // With the downloaded response apply the post processing middleware
        foreach($this->middleware as $middleware) {
            if(!$middleware instanceof ProcessResponseMiddlewareInterface) {
                continue;
            }
            $promise = $promise->then(function($message) use ($middleware, $request) {
                $this->logger->debug("Middleware manager: let the {middleware} middleware process the response", ['middleware' => get_class($middleware)]);
                $message = $middleware->processResponse($message, $request);
                if($message instanceof RequestInterface) {
                    return $this->download($message);
                }
                return $message;
            });
        }

        $promise->then(function ($message) use (&$downloaded, $request) {
            if($message instanceof ResponseInterface) {
                $this->logger->debug("Middleware manager: downloaded {uri}", ['uri' => $request->getUri()]);
                $this->events->dispatch(new ResponseDownloadedEvent($this, $message, $request));
                $downloaded->resolve($message);
            }
            return $message;
        }, function($e) use ($downloaded) {
            $downloaded->reject($e);
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