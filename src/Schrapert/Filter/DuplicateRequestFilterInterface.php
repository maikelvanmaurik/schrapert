<?php
namespace Schrapert\Filter;

use Schrapert\Crawl\RequestInterface;
use React\Promise\PromiseInterface;
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