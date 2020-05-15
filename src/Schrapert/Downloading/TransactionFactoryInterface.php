<?php

namespace Schrapert\Downloading;

use Schrapert\Http\RequestInterface;

interface TransactionFactoryInterface
{
    /**
     * @param RequestInterface $request
     * @return TransactionInterface
     */
    public function createTransaction(RequestInterface $request): TransactionInterface;
}
