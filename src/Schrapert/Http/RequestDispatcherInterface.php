<?php
namespace Schrapert\Http;

use Psr\Http\Message\RequestInterface;

interface RequestDispatcherInterface
{
    public function dispatch(RequestInterface $request);
}