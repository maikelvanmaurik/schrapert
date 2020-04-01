<?php
namespace Schrapert\Http\Cache;

use Schrapert\Http\RequestInterface;
use Schrapert\Http\ResponseInterface;

interface PolicyInterface
{
    public function isCachedResponseValid(ResponseInterface $cached, ResponseInterface $response, RequestInterface $request);

    public function shouldCacheRequest(RequestInterface $request);

    public function shouldCacheResponse(ResponseInterface $response, RequestInterface $request);

    public function isCachedResponseFresh(ResponseInterface $cachedResponse, RequestInterface $request);

}