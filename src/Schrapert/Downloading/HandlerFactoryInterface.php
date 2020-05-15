<?php

namespace Schrapert\Downloading;

use Schrapert\Downloading\RequestInterface;

interface HandlerFactoryInterface
{
    public function createHandler(RequestInterface $request) : HandlerInterface;
}
