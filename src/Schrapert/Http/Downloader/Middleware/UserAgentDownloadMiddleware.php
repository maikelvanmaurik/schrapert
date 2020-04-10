<?php

namespace Schrapert\Http\Downloader\Middleware;

use Schrapert\Http\RequestInterface;

class UserAgentDownloadMiddleware implements DownloadMiddlewareInterface, ProcessRequestMiddlewareInterface
{
    private $userAgent;

    public function __construct($userAgent = 'Schrapert')
    {
        $this->userAgent = $userAgent;
    }

    public function processRequest(RequestInterface $request)
    {
        if ($request instanceof RequestInterface && null === $request->getHeader('User-Agent')) {
            return $request->withHeader('User-Agent', $this->userAgent);
        }

        return $request;
    }
}
