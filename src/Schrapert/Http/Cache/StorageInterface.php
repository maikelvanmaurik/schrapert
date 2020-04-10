<?php

namespace Schrapert\Http\Cache;

use React\Promise\PromiseInterface;
use Schrapert\Http\RequestInterface;
use Schrapert\Http\ResponseInterface;

interface StorageInterface
{
    public function clear();

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function retrieveResponse(RequestInterface $request);

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return PromiseInterface
     */
    public function storeResponse(RequestInterface $request, ResponseInterface $response);
}
