<?php
namespace Schrapert\Core;

use React\Promise\Deferred;
use React\Promise\RejectedPromise;
use Schrapert\Crawl\DropRequestException;
use Schrapert\Crawl\RequestInterface;
use Schrapert\Filter\DuplicateRequestFilterInterface;
use Schrapert\Log\LoggerInterface;
use Schrapert\Schedule\SchedulerInterface;
use Schrapert\Signal\SignalManager;
use Schrapert\SpiderInterface;
use Schrapert\Util\DelayedCallbackFactory;
use Schrapert\Util\IntervalCallbackFactory;
use Exception;
use React\Promise\PromiseInterface;

class ExecutionEngine
{
    private $scheduler;
    /**
     * @var RequestProcessInterface[]
     */
    private $processes;

    private $startTime;

    private $spider;

    private $signals;

    private $processing;

    private $closing;

    private $paused;

    private $running;

    private $delayedCallbackFactory;

    private $intervalCallbackFactory;

    private $startRequests;
    /**
     * @var Deferred
     */
    private $closeWait;

    private $dupeFilter;

    private $startRequestIndex;

    /**
     * @var \Schrapert\Util\DelayedCallback
     */
    private $next;

    public function __construct(LoggerInterface $logger, SignalManager $signals, ScraperInterface $scraper, DuplicateRequestFilterInterface $dupeFilter = null, RequestProcessorFactoryInterface $requestProcessorFactory, SchedulerInterface $scheduler, IntervalCallbackFactory $intervalCallbackFactory, DelayedCallbackFactory $delayedCallbackFactory)
    {
        $this->logger = $logger;
        $this->dupeFilter = $dupeFilter;
        $this->scraper = $scraper;
        $this->intervalCallbackFactory = $intervalCallbackFactory;
        $this->delayedCallbackFactory = $delayedCallbackFactory;
        $this->signals = $signals;
        $this->requestProcessorFactory = $requestProcessorFactory;
        $this->scheduler = $scheduler;
        $this->paused = false;
        $this->downloading = [];
        $this->processes = [];
    }

    /**
     * @param RequestInterface $request
     * @param SpiderInterface $spider
     * @return PromiseInterface
     */
    public function schedule(RequestInterface $request, SpiderInterface $spider)
    {
        $this->signals->trigger('request-schedule', ['spider' => $spider, 'request' => $request]);
        return $this->scheduler->enqueueRequest($request)->then(function($scheduled) use ($spider, $request) {
            return true;
        }, function($error) use ($request, $spider) {
            if($error instanceof DropRequestException) {
                $this->signals->trigger('request-dropped', ['spider' => $spider, 'request' => $request]);
            } else {
                throw $error;
            }
        });
    }

    public function openSpider(SpiderInterface $spider, $requests)
    {
        $this->startRequests = iterator_to_array($requests);
        $this->spider = $spider;

        $this->scheduler->open($spider);
        $this->scraper->open($spider);
        if($this->dupeFilter) {
            $this->dupeFilter->open($spider);
        }
        $this->next = $this->delayedCallbackFactory->factory(function() use ($spider) {
            return $this->nextRequest($spider);
        }, [$spider]);
        $this->next->schedule();
        $loopingCallback = $this->intervalCallbackFactory->factory(array($this->next, 'schedule'));
        $loopingCallback->start(1);
    }

    private function needsBackOut(SpiderInterface $spider)
    {
        // Any running processes
        foreach($this->processes as $process) {
            if($process->needsBackOut()) {
                return true;
            }
        }
        return !$this->running || $this->closing || count($this->processes) > 10;
    }

    /**
     * Retrieve the next request from the scheduler, if there are no next requests (yet)
     * the promise will reject
     *
     * @param SpiderInterface $spider
     * @return PromiseInterface
     */
    private function nextRequestFromScheduler(SpiderInterface $spider)
    {
        return $this->scheduler->nextRequest()->then(function ($request) use ($spider) {
            return $this->process($request, $spider)->then(function() {

            });
        });
    }

    /**
     * @param RequestInterface $request
     * @param SpiderInterface $spider
     * @return PromiseInterface
     */
    private function process(RequestInterface $request, SpiderInterface $spider)
    {
        try {

            $this->logger->debug("Process request %s", [$request->getUri()]);

            $this->processing[] = $request;

            $processor = $this->requestProcessorFactory->factory($request);

            $process = $processor->process($this, $request, $spider);

            $this->processes[] = $process;

            $removeRequest = function() use ($request, $process) {

                $this->logger->debug("Process %s finished", [$request->getUri()]);

                // Remove the process & request
                $index = array_search($request, $this->processing, true);
                $this->logger->debug("Index of the request %s", [$index]);
                if (false !== $index) {
                    unset($this->processing[$index]);
                }
                $index = array_search($process, $this->processes, true);
                if (false !== $index) {
                    unset($this->processes[$index]);
                }

                $this->logger->debug("Number of pending processed %s, processing %s", [count($this->processes), count($this->processing)]);
            };

            return $process->run()
                ->then($removeRequest, $removeRequest)
                ->then();
        } catch(Exception $e) {
            return new RejectedPromise($e);
        }
    }

    private function scheduleCrawl(RequestInterface $request, SpiderInterface $spider)
    {
        $this->logger->debug("Schedule request %s", [$request->getUri()]);

        return $this->schedule($request, $spider)->then(function ($scheduled) {
            if($scheduled) {
                $this->next->schedule();
            }
            return true;
        }, function () {
            die('WTF!');
        });
    }

    public function crawl(RequestInterface $request, SpiderInterface $spider)
    {
        if(null !== $this->dupeFilter) {
            return $this->dupeFilter->isDuplicateRequest($request)->then(function ($isDuplicate) use ($request, $spider) {
                if($isDuplicate) {
                    $this->logger->debug("Request %s is a duplicate request", [$request->getUri()]);
                    throw new DropRequestException("Duplicate request");
                } else {
                    $this->logger->debug("Request %s is not a duplicate request", [$request->getUri()]);
                }
                return $this->scheduleCrawl($request, $spider);
            });
        }
        return $this->scheduleCrawl($request, $spider);
    }

    private function spiderIsIdle(SpiderInterface $spider)
    {
        $this->logger->debug("Check if spider idle");
        if(!$this->scraper->isIdle()) {
            $this->logger->debug("Not idle (scraper is not idle)");
            return false;
        }
        if(!empty($this->processing)) {
            $this->logger->debug("Not idle (still processing requests)");
            $this->logger->debug('this url ' . reset($this->processing)->getUri());
            return false;
        }

        if($this->startRequestIndex < count($this->startRequests)) {
            $this->logger->debug("Not idle (pending start requests)");
            return false;
        }
        if($this->scheduler->hasPendingRequests()) {
            $this->logger->debug("Not idle (pending scheduled requests)");
            return false;
        }

        $this->logger->debug("Spider is idle");

        return true;
    }

    private function spiderIdle(SpiderInterface $spider)
    {
        $this->logger->debug("Spider became idle");
    }

    private function nextRequest(SpiderInterface $spider)
    {
        if($this->paused) {
            return;
        }

        if(!$this->needsBackOut($spider)) {

            return $this->nextRequestFromScheduler($spider)->then(null, function() use ($spider) {

                // Fail to get next request
                if(!empty($this->startRequests) && !$this->needsBackOut($spider)) {
                    if(false !== ($request = current($this->startRequests))) {
                        $this->crawl($request, $spider);
                        // Move pointer to the next request
                        next($this->startRequests);
                        $this->startRequestIndex++;
                    }
                }

                $this->next->schedule();
            })->then(function() use ($spider) {
                if($this->spiderIsIdle($spider)) {
                    $this->maybeShutdownIdleSpider($spider);
                }
            });
        }
    }

    private function closeSpider(SpiderInterface $spider, $reason = 'cancelled')
    {
        $this->logger->info("Close spider");

        if($this->closing) {
            return $this->closing;
        }

        $deferred = new Deferred();
        $promise = $deferred->promise();

        $promise->then(function() {
           // Cancel the next call
            $this->next->cancel();
            return true;
        });

        $promise->then(function() use ($spider) {
            $this->scraper->closeSpider($spider);
        });

        $promise->then(function() use ($spider, $reason) {
           $this->scheduler->close($spider, $reason);
        });

        $promise->then(function() {
           return $this->closeWait->resolve(true);
        });

        $deferred->resolve(true);

        return $promise;
    }

    private function maybeShutdownIdleSpider($spider)
    {
        //TODO send signal to check if shutdown:
        $this->closeSpider($spider, 'finished');
    }

    public function start()
    {
        try {
            if ($this->running) {
                throw new \Exception("Already running");
            }
            $this->startTime = date_create();
            $this->logger->info("Started");
            $this->running = true;
            $this->closeWait = new Deferred();
            return $this->closeWait->promise();
        } catch(Exception $e) {
            return new RejectedPromise($e);
        }
    }

    public function stop()
    {
        if(!$this->running) {
            throw new \Exception("Not running");
        }
    }
}