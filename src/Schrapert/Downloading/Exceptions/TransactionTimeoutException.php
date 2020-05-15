<?php

namespace Schrapert\Downloading\Exception;

use Psr\Http\Message\RequestInterface;
use Schrapert\Downloading\DownloadTransaction;

class TransactionTimeoutException extends \RuntimeException
{
    private $transaction;

    private $request;

    public function __construct(DownloadTransaction $transaction, RequestInterface $request)
    {
        $this->transaction = $transaction;
        $this->request = $request;
        parent::__construct();
    }
}
