<?php

namespace Schrapert\Scheduling;

use Countable;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use Schrapert\Crawling\CrawlerInterface;
use Schrapert\Downloading\RequestInterface;

interface PriorityQueueInterface extends Countable
{
    /**
     * @param  RequestInterface  $request
     * @return Promise|boolean true if the request was pushed; otherwise, false
     */
    public function push(RequestInterface $request);

    /**
     * @return PromiseInterface
     */
    public function pop() : PromiseInterface;

    public function open(CrawlerInterface $crawler);

    public function close(CrawlerInterface $crawler);
}
