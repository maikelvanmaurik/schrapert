<?php

namespace Schrapert\Scheduling;

use React\Promise\PromiseInterface;
use Schrapert\Crawling\CrawlerInterface;
use Schrapert\Downloading\RequestInterface;
use Schrapert\Events\EventDispatcherInterface;
use Schrapert\Scheduling\Events\ScheduleRequest;

class Scheduler implements SchedulerInterface
{
    private $priorityQueue;

    private $spider;

    private $events;

    public function __construct(EventDispatcherInterface $events, PriorityQueueInterface $priorityQueue)
    {
        $this->events = $events;
        $this->priorityQueue = $priorityQueue;
    }

    public function open(CrawlerInterface $crawler)
    {
        $this->crawler = $crawler;
        $this->priorityQueue->open($crawler);
    }

    public function close(CrawlerInterface $crawler, $reason)
    {
        $this->priorityQueue->close($crawler);
    }

    /**
     * @param  RequestInterface  $request
     * @return PromiseInterface
     */
    public function schedule(RequestInterface $request)
    {
        $this->events->dispatch(new ScheduleRequest($request));
        // $this->logger->debug('Scheduler enqueue request {uri}', ['uri' => $req->getUri()]);
        return $this->priorityQueue->push($request)->then(function () {
            return true;
        });
    }

    public function nextRequest()
    {
        return $this->priorityQueue->pop();
    }

    public function count()
    {
        return count($this->priorityQueue);
    }

    public function hasPendingRequests()
    {
        return $this->priorityQueue->count() > 0;
    }
}
