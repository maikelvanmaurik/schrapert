<?php
namespace Schrapert\Http\Downloader;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use React\Stream\BufferedSink;
use React\Stream\ReadableStreamInterface;
use RuntimeException;
use Schrapert\Http\RequestDispatcherInterface;
use Schrapert\Http\ResponseException;
use Schrapert\Http\StreamFactoryInterface;
use Schrapert\Http\UriResolverInterface;
use Exception;

/**
 * Represents an HTTP transaction
 */
class DownloadTransaction implements DownloadTransactionInterface
{
    private $current;

    private $numRequests;

    private $dispatcher;

    private $streamFactory;

    private $options;

    public function __construct(RequestDispatcherInterface $dispatcher, UriResolverInterface $uriResolver, StreamFactoryInterface $streamFactory)
    {
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
        return $this->next($request);
    }

    public function next(RequestInterface $request)
    {
        $this->current = $request;

        $this->numRequests++;

        $result = $this->dispatcher->dispatch($request);

        if(!filter_var($this->getOption('streaming', false))) {
            $result = $result->then(array($this, 'bufferResponse'));
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
     * @internal
     * @param Exception $error
     * @param RequestInterface $request
     * @throws Exception
     */
    public function onError(Exception $error, RequestInterface $request)
    {
        throw $error;
    }

    /**
     * @internal
     * @param ResponseInterface $response
     * @return PromiseInterface Promise<ResponseInterface, Exception>
     */
    public function bufferResponse(ResponseInterface $response)
    {
        $stream = $response->getBody();
        // body is not streaming => already buffered
        if (!$stream instanceof ReadableStreamInterface) {
            return Promise\resolve($response);
        }
        // buffer stream and resolve with buffered body
        return BufferedSink::createPromise($stream)->then(function($body) use ($response) {
            return $response->withBody($this->streamFactory->createStream($body));
        });
    }

    private function onResponseRedirect(ResponseInterface $response, RequestInterface $request)
    {
        $maxRedirects = $this->getOption('max_redirects', 10);
        // resolve location relative to last request URI
        $nextUri = $this->uriResolver->resolve($request->getUri(), $response->getHeaderLine('Location'));
        $method = ($request->getMethod() === 'HEAD') ? 'HEAD' : 'GET';
        if ($this->numRequests >= $maxRedirects) {
            throw new RuntimeException('Maximum number of redirects (' . $this->maxRedirects . ') exceeded');
        }
        return $this->next($request->withUri($nextUri)->withMethod($method));
    }


    private function onResponse(ResponseInterface $response, RequestInterface $request)
    {
        $followRedirects = $this->getOption('follow_redirects', true);
        $obeySuccessCode = $this->getOption('obey_success_code', true);
        if ($followRedirects && ($response->getStatusCode() >= 300 && $response->getStatusCode() < 400)) {
            return $this->onResponseRedirect($response, $request);
        }
        // only status codes 200-399 are considered to be valid, reject otherwise
        if ($obeySuccessCode && ($response->getStatusCode() < 200 || $response->getStatusCode() >= 400)) {
            throw new ResponseException($response);
        }

        // resolve our initial promise
        return $response;
    }
}