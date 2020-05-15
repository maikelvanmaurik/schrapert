<?php

namespace Schrapert\Downloading\Middleware;

use React\Promise\Deferred;
use Schrapert\Crawling\Exception\IgnoreRequestException;
use Schrapert\Http\Cache\PolicyInterface;
use Schrapert\Http\Cache\RequestProcessorPolicyInterface;
use Schrapert\Http\Cache\StorageInterface;
use Schrapert\Http\RequestInterface;
use Schrapert\Http\ResponseInterface;

/**
 * Represents a middleware which provides a low-level cache to all HTTP requests and responses. It has to be combined
 * with a cache storage backend as well as a cache policy.
 *
 * @package Schrapert\Http\Crawling\Middleware
 */
class HttpCacheMiddleware implements DownloaderMiddlewareInterface, ProcessRequestMiddlewareInterface, ProcessResponseMiddlewareInterface
{
    private $storage;

    private $policy;

    private $ignoreMissing;

    public function __construct(StorageInterface $storage = null, PolicyInterface $policy, $ignoreMissing = false)
    {
        $this->policy = $policy;
        $this->storage = $storage;
        $this->ignoreMissing = $ignoreMissing;
    }

    /**
     * @return PolicyInterface
     */
    public function getPolicy()
    {
        return $this->policy;
    }

    /**
     * @param  PolicyInterface  $policy
     * @return HttpCacheMiddleware
     */
    public function withPolicy(PolicyInterface $policy)
    {
        $new = clone $this;
        $new->policy = $policy;
        return $new;
    }

    /**
     * @return StorageInterface
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @param  StorageInterface  $storage
     * @return HttpCacheMiddleware
     */
    public function withStorage(StorageInterface $storage)
    {
        $new = clone $this;
        $new->storage = $storage;
        return $new;
    }

    /**
     * @param  RequestInterface  $request
     * @return RequestInterface|ResponseInterface
     * @throws IgnoreRequestException when {@see ignoreMissing} is true and the response is not cached
     */
    public function processRequest(RequestInterface $request)
    {
        if ($request->getMetaData('skip_cache')) {
            return $request;
        }

        if (! $this->policy->shouldCacheRequest($request)) {
            return $request->withMetaData('_skip_cache', true);
        }

        $cached = $this->storage->retrieveResponse($request);
        if (! $cached) {
            if ($this->ignoreMissing) {
                throw new IgnoreRequestException('Ignored request not in cache');
            }
            return $request;
        }

        if ($this->policy instanceof RequestProcessorPolicyInterface) {
            $request = $this->policy->processRequest($cached, $request);
        }

        if ($this->policy->isCachedResponseFresh($cached, $request)) {
            return $cached->withMetaData('request', $request);
        }

        return $request->withMetaData('cached_response', $cached);
    }

    private function cache(RequestInterface $request, ResponseInterface $response)
    {
        $deferred = new Deferred();
        if ($this->policy->shouldCacheResponse($response, $request)) {
            return $this->storage->storeResponse($request, $response)->then(function () use ($response, $deferred) {
                $deferred->resolve($response);
            }, function ($e) use ($deferred) {
                $deferred->reject($e);
            });
        } else {
            $deferred->resolve($response);
        }

        return $deferred->promise();
    }

    public function processResponse(ResponseInterface $response, RequestInterface $request)
    {
        if ($request->getMetaData('skip_cache', false)) {
            return $response;
        }

        // When the response does not contain a Date header add it
        if (! $response->getHeaderLine('Date')) {
            $response = $response->withHeader('Date', gmdate('r'));
        }

        if (null === ($cached = $request->getMetaData('cached_response'))) {
            return $this->cache($request, $response)->then(function () use ($response) {
                return $response;
            });
        }

        if ($this->policy->isCachedResponseValid($cached, $response, $request)) {
            return $cached;
        }

        return $this->cache($request, $response)->then(function () use ($response) {
            return $response;
        });
    }
}
