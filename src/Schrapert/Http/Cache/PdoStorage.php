<?php

namespace Schrapert\Http\Cache;

use PDO;
use React\Promise\PromiseInterface;
use Schrapert\Downloading\RequestFingerprintGeneratorInterface;
use Schrapert\Http\RequestInterface;
use Schrapert\Http\ResponseInterface;

class PdoStorage implements StorageInterface
{
    private $pdo;

    public function __construct(RequestFingerprintGeneratorInterface $fingerPrinter)
    {
        $this->fingerPrinter = $fingerPrinter;
    }

    public function clear()
    {
    }

    public function withPdo(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @param RequestInterface $request
     * @return PromiseInterface
     */
    public function retrieveResponse(RequestInterface $request)
    {
        $key = $this->fingerPrinter->fingerprint($request);
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return PromiseInterface
     */
    public function storeResponse(RequestInterface $request, ResponseInterface $response)
    {
        // TODO: Implement storeResponse() method.
    }
}
