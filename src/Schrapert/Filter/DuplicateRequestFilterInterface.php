<?php

namespace Schrapert\Filter;

use React\Promise\PromiseInterface;
use Schrapert\Downloading\RequestInterface;
use Schrapert\SpiderInterface;

interface DuplicateRequestFilterInterface
{
    /**
     * @param RequestInterface $request
     * @return PromiseInterface
     */
    public function isDuplicateRequest(RequestInterface $request);

    public function open(SpiderInterface $spider);
}
