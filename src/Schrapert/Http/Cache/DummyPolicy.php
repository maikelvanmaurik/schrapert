<?php

namespace Schrapert\Http\Cache;

use Schrapert\Http\RequestInterface;
use Schrapert\Http\ResponseInterface;

class DummyPolicy implements PolicyInterface
{
    private $ignoreSchemes;

    private $ignoreHttpCodes;

    public function __construct(array $ignoreSchemes = ['file'], array $ignoreHttpCodes = [])
    {
        $this->ignoreSchemes = $ignoreSchemes;
        $this->ignoreHttpCodes = $ignoreHttpCodes;
    }

    public function isCachedResponseValid(ResponseInterface $cached, ResponseInterface $response, RequestInterface $request)
    {
        return true;
    }

    public function shouldCacheRequest(RequestInterface $request)
    {
        return ! in_array($request->getUri()->getScheme(), $this->ignoreSchemes);
    }

    public function shouldCacheResponse(ResponseInterface $response, RequestInterface $request)
    {
        return ! in_array($response->getStatusCode(), $this->ignoreHttpCodes);
    }

    public function isCachedResponseFresh(ResponseInterface $response, RequestInterface $request)
    {
        return true;
    }
}
