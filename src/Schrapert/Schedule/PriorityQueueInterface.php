<?php

namespace Schrapert\Schedule;

use Countable;
use React\Promise\Promise;
use Schrapert\Crawl\RequestInterface;
use Schrapert\SpiderInterface;

interface PriorityQueueInterface extends Countable
{
    /**
     * @param RequestInterface $request
     * @return Promise|bool true if the request was pushed; otherwise, false
     */
    public function push(RequestInterface $request);

    /**
     * @return PromiseInterface
     */
    public function pop();

    public function open(SpiderInterface $spider);

    public function close(SpiderInterface $spider);
}
