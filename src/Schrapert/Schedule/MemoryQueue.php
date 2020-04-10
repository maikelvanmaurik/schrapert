<?php

namespace Schrapert\Schedule;

use Exception;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use Schrapert\Crawl\RequestInterface;
use Schrapert\SpiderInterface;

class MemoryQueue implements PriorityQueueInterface
{
    public function open(SpiderInterface $spider)
    {
    }

    public function close(SpiderInterface $spider)
    {
    }

    /**
     * @param RequestInterface $request
     * @return Deferred true if the request was pushed; otherwise, false
     */
    public function push(RequestInterface $request)
    {
        throw new Exception('TODO!');
    }

    /**
     * @return PromiseInterface request when there a still requests inside the queue; otherwise, false.
     */
    public function pop()
    {
        throw new Exception('TODO!');
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object.
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     */
    public function count()
    {
        return 0;
    }
}
