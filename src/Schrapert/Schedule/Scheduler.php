<?php
namespace Schrapert\Schedule;

use React\Promise\Deferred;
use React\Promise\RejectedPromise;
use Schrapert\Filter\DuplicateRequestFilterInterface;
use Schrapert\Crawl\RequestInterface;
use Schrapert\Log\LoggerInterface;
use Schrapert\SpiderInterface;
use React\Promise\PromiseInterface;

class Scheduler implements SchedulerInterface
{
    private $priorityQueue;

    private $spider;

    private $logger;

    public function __construct(LoggerInterface $logger, PriorityQueueInterface $priorityQueue)
    {
        $this->logger = $logger;
        $this->priorityQueue = $priorityQueue;
    }

    public function open(SpiderInterface $spider)
    {
        $this->spider = $spider;
        $this->priorityQueue->open($spider);
    }

    public function close(SpiderInterface $spider, $reason)
    {
        $this->priorityQueue->close($spider);
    }

    /**
     * @param RequestInterface $req
     * @return PromiseInterface
     */
    public function enqueueRequest(RequestInterface $req)
    {
        $this->logger->debug("Scheduler enqueue request {uri}", ['uri' => $req->getUri()]);
        return $this->priorityQueue->push($req)->then(function() {
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