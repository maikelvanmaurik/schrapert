<?php

namespace Schrapert\Core;

use Schrapert\Downloading\RequestInterface;
use Schrapert\SpiderInterface;

interface RequestProcessorInterface
{
    /**
     * @param RequestInterface $request
     * @param SpiderInterface $spider
     * @return RequestProcessInterface
     */
    public function process(ExecutionEngine $engine, RequestInterface $request, SpiderInterface $spider);
}
