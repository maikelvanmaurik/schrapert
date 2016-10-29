<?php
namespace Schrapert\Http\Downloader\Middleware;

use Schrapert\Http\RequestInterface;

class DefaultHeadersMiddleware implements DownloadMiddlewareInterface, ProcessRequestMiddlewareInterface
{
    private $headers;

    public function __construct()
    {
        $this->headers = [];
    }

    public function withHeader($name, $value)
    {
        $new = clone $this;
        $new->headers[$name] = $value;
        return $new;
    }

    public function withHeaders($headers)
    {
        $new = clone $this;
        $new->headers = array_merge($new->headers, $headers);
        return $new;
    }

    public function processRequest(RequestInterface $request)
    {
        foreach($this->headers as $name => $value) {
            if(!$request->hasHeader($name)) {
                $request = $request->withHeader($name, $value);
            }
        }
        return $request;
    }
}