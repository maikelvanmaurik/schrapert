<?php
namespace Schrapert\Http\Downloader\Middleware;

use React\Promise\FulfilledPromise;
use Schrapert\Http\Downloader\DownloadResponse;
use Schrapert\Http\RequestInterface;
use Schrapert\Http\ResponseBuilderInterface;
use Schrapert\Http\ResponseReaderFactoryInterface;
use Schrapert\Http\ResponseReaderResultInterface;
use Schrapert\Log\LoggerInterface;
use Schrapert\SpiderInterface;

class DownloadCompressionMiddleware implements DownloadMiddlewareInterface, ProcessRequestMiddlewareInterface, ProcessResponseMiddlewareInterface
{
    private $logger;

    private $responseBuilder;

    private $readerFactory;

    public function __construct(LoggerInterface $logger, ResponseBuilderInterface $responseBuilder, ResponseReaderFactoryInterface $readerFactory)
    {
        $this->logger = $logger;
        $this->responseBuilder = $responseBuilder;
        $this->readerFactory = $readerFactory;
    }

    public function processRequest(RequestInterface $request, SpiderInterface $spider)
    {
        $request->setHeader('Accept-Encoding', 'gzip,deflate');
        return $request;
    }

    private function decode($raw)
    {
        //TODO non-blocking gzip decoding
        return new FulfilledPromise(gzdecode($raw));
    }

    public function processResponse(DownloadResponse $response, SpiderInterface $spider)
    {
        $headers = $response->getHeaders();
        if(!empty($headers['Content-Encoding'])) {

            $reader = $this->readerFactory->factory($response);

            return $reader->readToEnd()->then(function(ResponseReaderResultInterface $result) use ($response) {
                // Decode the body
                return $this->decode($result->getBody())->then(function($body) use ($result, $response) {
                    $this->responseBuilder->setBody($body);
                    $this->responseBuilder->setHeaders($response->getHeaders());
                    return $this->responseBuilder->build();
                });
            });
        }
        return $response;
    }
}