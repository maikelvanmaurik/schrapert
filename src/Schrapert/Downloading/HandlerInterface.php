<?php
namespace Schrapert\Downloading;

use React\Promise\PromiseInterface;

interface HandlerInterface
{
    public function download(RequestInterface $request) : PromiseInterface;
}
