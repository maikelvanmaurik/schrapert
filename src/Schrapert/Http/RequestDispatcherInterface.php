<?php

namespace Schrapert\Http;

use Psr\Http\Message\RequestInterface as PsrRequestInterface;

interface RequestDispatcherInterface
{
    public function dispatch(PsrRequestInterface $request);
}
