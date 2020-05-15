<?php

namespace Schrapert\Downloading;

use Closure;

class Request extends Message implements RequestInterface
{
    private UriInterface $uri;

    private ?Closure $callback;

    private $stream;

    /**
     * Request constructor.
     * @param  UriInterface|string  $uri
     */
    public function __construct($uri, $body = '')
    {
        parent::__construct();
        if (! $uri instanceof UriInterface) {
            $uri = new Uri($uri);
        }
        $this->uri = $uri;

        if ($body !== '' && $body !== null) {
            $this->stream = stream_for($body);
        }
    }

    public function withUri($uri) : self
    {
        if (!$uri instanceof UriInterface) {
            $uri = new Uri($uri);
        }
        $clone = clone $this;
        $clone->uri = $uri;
        return $clone;
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function getBody()
    {
        return $this->stream;
    }
}
