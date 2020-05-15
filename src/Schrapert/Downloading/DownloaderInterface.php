<?php

namespace Schrapert\Downloading;

use React\Promise\PromiseInterface;

interface DownloaderInterface
{
    /**
     * @param RequestInterface $request
     * @param RequestOptionsInterface $options
     * @return PromiseInterface
     */
    public function download(RequestInterface $request, ?RequestOptionsInterface $options = null) : PromiseInterface;
}
