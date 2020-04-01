<?php
namespace Schrapert\Http\Cache;

use Schrapert\Http\RequestInterface;
use Schrapert\Http\ResponseInterface;

interface RequestProcessorPolicyInterface
{
    /**
     * Processes the request allowing a policy to add additional headers etc. before
     * dispatching the request
     *
     * @param ResponseInterface $cachedResponse
     * @param RequestInterface $request
     * @return RequestInterface
     */
    public function processRequest(ResponseInterface $cachedResponse, RequestInterface $request);
}