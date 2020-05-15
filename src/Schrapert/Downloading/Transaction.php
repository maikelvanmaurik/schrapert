<?php

namespace Schrapert\Downloading;

use Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\TimerInterface;
use React\Promise;
use React\Promise\PromiseInterface;
use React\Stream\BufferedSink;
use React\Stream\ReadableStreamInterface;
use Schrapert\Downloading\Exception\TransactionTimeoutException;
use Schrapert\Http\RequestDispatcherInterface;
use Schrapert\Http\StreamFactoryInterface;
use Schrapert\Http\UriResolverInterface;

/**
 * Represents an download transaction.
 */
class Transaction implements TransactionInterface
{
    private $loop;

    private $current;

    private $numRequests;

    private $dispatcher;

    private $streamFactory;

    private $options;
    /**
     * @var Promise\Deferred
     */
    private $deferred;
    /**
     * @var TimerInterface
     */
    private $timeoutTimer;

    public function __construct(
        LoopInterface $loop,
        RequestDispatcherInterface $dispatcher,
        UriResolverInterface $uriResolver,
        StreamFactoryInterface $streamFactory
    ) {
        $this->loop = $loop;
        $this->numRequests = 0;
        $this->dispatcher = $dispatcher;
        $this->uriResolver = $uriResolver;
        $this->streamFactory = $streamFactory;
    }

    public function withOption($key, $value)
    {
        $new = clone $this;
        $new->options[$key] = $value;
        return $new;
    }

    public function withOptions(array $options = [])
    {
        $new = clone $this;
        $new->options = $options;
        return $new;
    }

    public function getOption($key, $default = null)
    {
        return array_key_exists($key, $this->options) ? $this->options[$key] : $default;
    }

    public function send(RequestInterface $request)
    {
        $this->deferred = new Promise\Deferred();
        $this->next($request)->then(function ($response) {
            $this->deferred->resolve($response);
        }, function ($e) {
            $this->deferred->reject($e);
        });
        if (null !== ($timeout = $this->getOption('download_timeout'))) {
            $this->timeoutTimer = $this->loop->addTimer($timeout, function () use ($request) {
                $this->deferred->reject(new TransactionTimeoutException($this, $request));
            });
        }
        return $this->deferred->promise();
    }

    /**
     * @param RequestInterface $request
     * @return PromiseInterface
     */
    public function next(RequestInterface $request)
    {
        $this->current = $request;

        $this->numRequests++;

        $result = $this->dispatcher->dispatch($request);

        if (! filter_var($this->getOption('streaming', false))) {
            $result = $result->then([$this, 'bufferResponse']);
        }

        return $result->then(
            function (ResponseInterface $response) use ($request) {
                return $this->onResponse($response, $request);
            },
            function ($error) use ($request) {
                return $this->onError($error, $request);
            }
        );
    }

    /**
     * @param Exception $error
     * @param RequestInterface $request
     * @throws Exception
     * @internal
     */
    public function onError(Exception $error, RequestInterface $request)
    {
        throw $error;
    }

    /**
     * @param ResponseInterface $response
     * @return PromiseInterface Promise<ResponseInterface, Exceptions>
     * @internal
     */
    public function bufferResponse(ResponseInterface $response)
    {
        $stream = $response->getBody();
        // body is not streaming => already buffered
        if (! $stream instanceof ReadableStreamInterface) {
            return Promise\resolve($response);
        }
        // buffer stream and resolve with buffered body
        return BufferedSink::createPromise($stream)->then(function ($body) use ($response) {
            return $response->withBody($this->streamFactory->createStream($body));
        });
    }

    private function onResponse(ResponseInterface $response, RequestInterface $request)
    {
        if ($this->timeoutTimer instanceof TimerInterface) {
            $this->timeoutTimer->cancel();
        }
        // resolve our initial promise
        return $response;
    }
}
