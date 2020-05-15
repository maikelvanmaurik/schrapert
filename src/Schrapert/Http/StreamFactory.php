<?php

namespace Schrapert\Http;

use Psr\Http\Message\StreamInterface;

class StreamFactory implements StreamFactoryInterface
{
    /**
     * @param string|resource|StreamInterface|null $resource
     * @param array $options
     * @return StreamInterface
     */
    public function createStream($resource = null, array $options = [])
    {
        if (is_scalar($resource)) {
            $stream = fopen('php://temp', 'r+');
            if ($resource !== '') {
                fwrite($stream, $resource);
                fseek($stream, 0);
            }
            return new Stream($stream, $options);
        }
        switch (gettype($resource)) {
            case 'resource':
                return new Stream($resource, $options);
            case 'object':
                if ($resource instanceof StreamInterface) {
                    return $resource;
                } elseif ($resource instanceof \Iterator) {
                    return new PumpStream(function () use ($resource) {
                        if (! $resource->valid()) {
                            return false;
                        }
                        $result = $resource->current();
                        $resource->next();
                        return $result;
                    }, $options);
                } elseif (method_exists($resource, '__toString')) {
                    return $this->createStream((string) $resource, $options);
                }
                break;
            case 'NULL':
                return new Stream(fopen('php://temp', 'r+'), $options);
        }
        if (is_callable($resource)) {
            return new PumpStream($resource, $options);
        }
        throw new \InvalidArgumentException('Invalid resource type: '.gettype($resource));
    }
}
