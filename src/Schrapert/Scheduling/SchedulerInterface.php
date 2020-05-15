<?php

namespace Schrapert\Scheduling;

use Countable;
use React\Promise\PromiseInterface;
use Schrapert\Crawling\CrawlerInterface;
use Schrapert\Downloading\RequestInterface;
use Schrapert\SpiderInterface;

/**
 * The Scheduler receives requests from the engine and enqueues them for
 * feeding them later (also to the engine) when the engine requests them.
 *
 * @package Schrapert\Core
 */
interface SchedulerInterface extends Countable
{
    /**
     * @param  RequestInterface  $request
     * @return PromiseInterface
     */
    public function schedule(RequestInterface $request);

    /**
     * @return PromiseInterface
     */
    public function nextRequest();

    public function open(CrawlerInterface $crawler);

    public function close(CrawlerInterface $crawler, $reason);

    public function hasPendingRequests();
}
