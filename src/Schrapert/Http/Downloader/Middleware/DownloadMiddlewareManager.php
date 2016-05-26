<?php
namespace Schrapert\Http\Downloader\Middleware;

use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use Schrapert\Http\RequestInterface;
use Schrapert\Log\LoggerInterface;
use Schrapert\SpiderInterface;

class DownloadMiddlewareManager implements DownloadMiddlewareManagerInterface
{
    private $middleware;

    private $logger;

    public function __construct(array $middleware = [], LoggerInterface $logger)
    {
        $this->logger = $logger;
        if (!empty($middleware)) {
            foreach ($middleware as $item) {
                $this->addMiddleware($item);
            }
        }
    }

    public function addMiddleware(DownloadMiddlewareInterface $middleware)
    {
        $this->middleware[] = $middleware;
    }

    public function download(callable $download, RequestInterface $request, SpiderInterface $spider)
    {
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
            $promise = $promise->then(function ($request) use ($middleware, $spider, &$promise, &$downloaded) {
                $result = $middleware->processRequest($request, $spider);
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
        $promise = $promise->then(function ($request) use ($download) {
            $this->logger->debug("Invoke request %s", [$request->getUri()]);
            // Here the request will be processed by all middleware
            return call_user_func($download, $request);
        });

        // With the downloaded response apply the post processing middleware
        foreach($this->middleware as $middleware) {
            if(!$middleware instanceof ProcessResponseMiddlewareInterface) {
                continue;
            }
            $promise = $promise->then(function ($response) use ($middleware, $spider) {
                $this->logger->debug("Middleware manager: let the %s middleware process the response", [get_class($middleware)]);
                return $middleware->processResponse($response, $spider);
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
}