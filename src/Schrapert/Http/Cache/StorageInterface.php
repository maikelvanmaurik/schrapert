<?php
namespace Schrapert\Http\Cache;

use Schrapert\Http\RequestInterface;
use Schrapert\Http\ResponseInterface;
use React\Promise\PromiseInterface;

interface StorageInterface
{
    public function clear();
    /**
     * @param RequestInterface $request
     * @return PromiseInterface
     */
    public function retrieveResponse(RequestInterface $request);

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return PromiseInterface
     */
    public function storeResponse(RequestInterface $request, ResponseInterface $response);
}