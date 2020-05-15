<?php

namespace Schrapert\Scheduling;

use Exception;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use Schrapert\Crawling\CrawlerInterface;
use Schrapert\Downloading\RequestInterface;

class MemoryQueue implements PriorityQueueInterface
{
    public function open(CrawlerInterface $crawler)
    {
    }

    public function close(CrawlerInterface $crawler)
    {
    }

    /**
     * @param  RequestInterface  $request
     * @return Deferred true if the request was pushed; otherwise, false
     */
    public function push(RequestInterface $request)
    {
        throw new Exception('TODO!');
    }

    /**
     * @return PromiseInterface request when there a still requests inside the queue; otherwise, false.
     */
    public function pop(): PromiseInterface
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
