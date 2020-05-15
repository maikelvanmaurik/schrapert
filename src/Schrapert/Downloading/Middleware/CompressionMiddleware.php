<?php

namespace Schrapert\Downloading\Middleware;

use React\Promise\FulfilledPromise;
use Schrapert\Http\RequestInterface;
use Schrapert\Http\ResponseInterface;
use Schrapert\Http\StreamFactoryInterface;
use Schrapert\Log\LoggerInterface;

class CompressionMiddleware implements DownloaderMiddlewareInterface, ProcessRequestMiddlewareInterface, ProcessResponseMiddlewareInterface
{
    private $logger;

    private $streamFactory;

    public function __construct(LoggerInterface $logger, StreamFactoryInterface $streamFactory)
    {
        $this->logger = $logger;
        $this->streamFactory = $streamFactory;
    }

    public function processRequest(RequestInterface $request)
    {
        return $request->withHeader('Accept-Encoding', 'gzip,deflate');
    }

    private function decode($raw)
    {
        $raw = (string)$raw;
        //TODO non-blocking gzip decoding
        return new FulfilledPromise(gzdecode($raw));
    }

    public function processResponse(ResponseInterface $response, RequestInterface $request)
    {
        $headers = $response->getHeaderLine('Content-Encoding');
        if (! empty($headers)) {
            // Decode the body
            return $this->decode($response->getBody())->then(function ($body) use ($response) {
                return $response->withBody($this->streamFactory->createStream($body));
            });
        }
        return $response;
    }
}
