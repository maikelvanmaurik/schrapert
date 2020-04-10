<?php

namespace Schrapert\Schedule;

use Schrapert\Crawl\RequestInterface;
use Schrapert\SpiderInterface;

/**
 * Default implementation of the priority queue which is a composite of a memory and disk queue.
 */
class PriorityQueue implements PriorityQueueInterface
{
    private $mq;

    private $dq;

    public function __construct(MemoryQueue $mq, DiskQueue $dq)
    {
        $this->mq = $mq;
        $this->dq = $dq;
    }

    public function open(SpiderInterface $spider)
    {
        $this->mq->open($spider);
        $this->dq->open($spider);
    }

    public function close(SpiderInterface $spider)
    {
        $this->mq->close($spider);
        $this->dq->close($spider);
    }

    public function push(RequestInterface $request)
    {
        return $this->dq->push($request)->then(function () {
            return true;
        }, function () use ($request) {
            // When failed use the memory queue
            return $this->mq->push($request);
        });
    }

    public function pop()
    {
        return $this->dq->pop()->then(function (RequestInterface $request) {
            return $request;
        }, function () {
            return $this->mq->pop();
        });
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
        return $this->mq->count() + $this->dq->count();
    }
}
