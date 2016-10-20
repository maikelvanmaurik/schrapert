<?php
namespace Schrapert\Http\Downloader;

use Psr\Http\Message\RequestInterface;

interface DownloadTransactionInterface
{
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