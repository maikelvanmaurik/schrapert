<?php

namespace Schrapert\Downloading;

use Psr\Http\Message\RequestInterface;
use React\Promise\PromiseInterface;

interface TransactionInterface
{
    /**
     * @param RequestInterface $request
     * @return PromiseInterface
     */
    public function send(RequestInterface $request);

    /**
     * @param $key
     * @param $value
     * @return TransactionInterface
     */
    public function withOption($key, $value);

    /**
     * @param array $options
     * @return TransactionInterface
     */
    public function withOptions(array $options = []);
}
