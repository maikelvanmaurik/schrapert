<?php
namespace Schrapert\Http\Downloader;

use Schrapert\Http\RequestInterface;

interface DownloadTransactionFactoryInterface
{
    /**
     * @param RequestInterface $request
     * @return DownloadTransactionInterface
     */
    public function createTransaction(RequestInterface $request);
}