<?php
namespace Schrapert\Http\Downloader\Exception;

use Psr\Http\Message\RequestInterface;
use Schrapert\Http\Downloader\DownloadTransaction;

class TransactionTimeoutException extends \RuntimeException
{
    private $transaction;

    private $request;

    public function __construct(DownloadTransaction $transaction, RequestInterface $request)
    {
        parent::__construct('Transaction timeout');
        $this->transaction = $transaction;
        $this->request = $request;
    }
}