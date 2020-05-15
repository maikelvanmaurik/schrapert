<?php
namespace Schrapert\Downloading;

use Schrapert\Downloading\RequestInterface;
use Schrapert\DI\Container;

class HandlerFactory implements HandlerFactoryInterface
{
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function createHandler(RequestInterface $request): HandlerInterface
    {
    }
}
