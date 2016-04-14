<?php
namespace Schrapert\Schedule;

use Schrapert\Crawl\RequestInterface;
use React\Promise\Promise;
use Countable;
use Schrapert\SpiderInterface;

interface PriorityQueueInterface extends Countable
{
    /**
     * @param RequestInterface $request
     * @return Promise|boolean true if the request was pushed; otherwise, false
     */
    public function push(RequestInterface $request);

    /**
     * @return PromiseInterface
     */
    public function pop();

    public function open(SpiderInterface $spider);

    public function close(SpiderInterface $spider);
}