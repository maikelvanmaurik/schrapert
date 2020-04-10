<?php

namespace Schrapert\Schedule;

use Countable;
use React\Promise\PromiseInterface;
use Schrapert\Crawl\RequestInterface;
use Schrapert\SpiderInterface;

/**
 * The Scheduler receives requests from the engine and enqueues them for
 * feeding them later (also to the engine) when the engine requests them.
 */
interface SchedulerInterface extends Countable
{
    /**
     * @param RequestInterface $request
     * @return PromiseInterface
     */
    public function enqueueRequest(RequestInterface $request);

    /**
     * @return PromiseInterface
     */
    public function nextRequest();

    public function open(SpiderInterface $spider);

    public function close(SpiderInterface $spider, $reason);

    public function hasPendingRequests();
}
