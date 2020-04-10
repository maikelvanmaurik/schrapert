<?php

namespace Schrapert\Http\Downloader;

use Psr\Http\Message\RequestInterface;
use React\Promise\PromiseInterface;

interface DownloadTransactionInterface
{
    /**
     * @param RequestInterface $request
     * @return PromiseInterface
     */
    public function send(RequestInterface $request);

    /**
     * @param $key
     * @param $value
     * @return DownloadTransactionInterface
     */
    public function withOption($key, $value);

    /**
     * @param array $options
     * @return DownloadTransactionInterface
     */
    public function withOptions(array $options = []);
}
