<?php
namespace Schrapert\Http;

use React\Stream\ReadableStreamInterface;

class ResponseFactory implements ResponseFactoryInterface
{
    private $streamFactory;

    public function __construct(StreamFactoryInterface $streamFactory)
    {
        $this->streamFactory = $streamFactory;
    }

    public function createResponse($version, $code, $reasonPhrase, $headers, $body)
    {
        return new Response($code, $this->body($body), $headers, $version, $reasonPhrase);
    }

    private function body($body)
    {
        if ($body instanceof ReadableStreamInterface) {
            return new ReadableBodyStream($body);
        }

        return $this->streamFactory->createStream($body);
    }
}