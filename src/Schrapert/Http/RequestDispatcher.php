<?php

namespace Schrapert\Http;

use Psr\Http\Message\RequestInterface as PsrRequestInterface;
use React\HttpClient\Client;
use React\HttpClient\Response as ResponseStream;
use React\Promise;
use React\Promise\Deferred;
use React\Stream\ReadableStreamInterface;

class RequestDispatcher implements RequestDispatcherInterface
{
    private $client;

    private $responseFactory;

    public function __construct(Client $client, ResponseFactoryInterface $responseFactory)
    {
        $this->client = $client;
        $this->responseFactory = $responseFactory;
    }

    public function dispatch(PsrRequestInterface $request)
    {
        $uri = $request->getUri();
        // URIs are required to be absolute for the HttpClient to work
        if ($uri->getScheme() === '' || $uri->getHost() === '') {
            return Promise\reject(new \InvalidArgumentException('Dispatching request requires absolute URI with scheme and host'));
        }
        $body = $request->getBody();

        $headers = [];
        foreach ($request->getHeaders() as $name => $values) {
            $headers[$name] = implode(', ', $values);
        }
        $deferred = new Deferred();
        $requestStream = $this->client->request($request->getMethod(), (string)$uri, $headers);
        $requestStream->on('error', function ($error) use ($deferred) {
            $deferred->reject($error);
        });
        $requestStream->on('response', function (ResponseStream $responseStream) use ($deferred) {
            $response = $this->responseFactory->createResponse(
                $responseStream->getVersion(),
                $responseStream->getCode(),
                $responseStream->getReasonPhrase(),
                $responseStream->getHeaders(),
                $responseStream
            );

            // apply response header values from response stream
            $deferred->resolve($response);
        });
        if ($body instanceof ReadableStreamInterface) {
            if ($body->isReadable()) {
                if ($request->hasHeader('Content-Length')) {
                    // length is known => just write to request
                    $body->pipe($requestStream);
                } else {
                    // length unknown => apply chunked transfer-encoding
                    // this should be moved somewhere else obviously
                    $body->on('data', function ($data) use ($requestStream) {
                        $requestStream->write(dechex(strlen($data))."\r\n".$data."\r\n");
                    });
                    $body->on('end', function () use ($requestStream) {
                        $requestStream->end("0\r\n\r\n");
                    });
                }
            } else {
                // stream is not readable => end request without body
                $requestStream->end();
            }
        } else {
            // body is fully buffered => write as one chunk
            $requestStream->end((string)$body);
        }
        return $deferred->promise();
    }
}
