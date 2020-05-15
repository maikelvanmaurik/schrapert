<?php

namespace Schrapert\Crawling;

use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use React\Promise\RejectedPromise;
use Schrapert\Core\Event\RequestDroppedEvent;
use Schrapert\Core\Event\ScheduleRequestEvent;
use Schrapert\Core\Event\SpiderClosed;
use Schrapert\Core\Event\SpiderOpened;
use Schrapert\Core\ExecutionEngine;
use Schrapert\Core\RequestProcessInterface;
use Schrapert\Events\Event;
use Schrapert\Events\EventDispatcherInterface;
use Schrapert\Feature\FeatureInterface;

use function React\Promise\reject;

/**
 * The runner provides the functionality to start running given spiders.
 *
 * @package Schrapert
 */
class Kernel
{
    /**
     * @var CrawlerInterface[]
     */
    private array $crawlers;
    /**
     * @var LoopInterface
     */
    private LoopInterface $loop;
    /**
     * @var EventDispatcherInterface
     */
    private EventDispatcherInterface $events;

    public function __construct(LoopInterface $loop, EventDispatcherInterface $events)
    {
        $this->crawlers = [];
        $this->events = $events;
        $this->loop = $loop;
    }

    public function run($spiders)
    {
        $finished = 0;
        foreach ((array) $spiders as $spider) {
            $this->open($spider)->then(function () use (&$finished) {
                $finished++;
                if ($finished == count($this->spiders)) {
                    if ($this->loop) {
                        $this->loop->stop();
                    }
                }
            });
        }
        $this->loop->run(); // Blocking call
    }

    public function stop()
    {
    }


    /**
     * @param  RequestInterface  $request
     * @param  SpiderInterface  $spider
     * @return PromiseInterface
     */
    public function schedule(RequestInterface $request, SpiderInterface $spider)
    {
        $this->events->dispatch(new ScheduleRequestEvent($request, $spider));

        return $this->scheduler->enqueueRequest($request)->then(function ($scheduled) use ($spider, $request) {
            return true;
        }, function ($error) use ($request, $spider) {
            if ($error instanceof DropRequestException) {
                $this->events->dispatch(new RequestDroppedEvent($request, $spider));
            } else {
                throw $error;
            }
        });
    }

    /**
     * @param  SpiderInterface  $spider
     * @param $requests
     * @internal
     */
    private function open(SpiderInterface $spider, $requests)
    {
        $this->startRequests = is_array($requests) ? $requests : iterator_to_array($requests);
        $this->spider = $spider;

        $this->scheduler->open($spider);
        $this->scraper->open($spider);
        if ($this->dupeFilter) {
            $this->dupeFilter->open($spider);
        }
        $this->next = $this->delayedCallbackFactory->factory(function () use ($spider) {
            return $this->nextRequest($spider);
        }, [$spider]);
        $this->next->schedule();
        $this->heartbeat = $this->intervalCallbackFactory->factory([$this->next, 'schedule']);
        $this->heartbeat->start(5);
        $this->events->dispatch(new SpiderOpened($spider));
    }

    private function needsBackOut(SpiderInterface $spider)
    {
        // Any running processes
        foreach ($this->processes as $process) {
            if ($process->needsBackOut()) {
                return true;
            }
        }
        return !$this->running || $this->closing || count($this->processes) > 10;
    }

    public function download(RequestInterface $request): PromiseInterface
    {
    }

    /**
     * Retrieve the next request from the scheduler, if there are no next requests (yet)
     * the promise will reject.
     *
     * @param  SpiderInterface  $spider
     * @return PromiseInterface
     */
    private function nextRequestFromScheduler(SpiderInterface $spider)
    {
        return $this->scheduler->nextRequest()->then(function ($request) use ($spider) {
            return $this->process($request, $spider);
        });
    }

    private function requestProcessFinished(RequestInterface $request, RequestProcessInterface $process = null)
    {
        $this->logger->debug('Process {uri} finished', ['uri' => $request->getUri()]);

        // Remove the process & request
        $index = array_search($request, $this->processing, true);
        $this->logger->debug('Index of the request {uri} -> {index}', ['uri' => $request->getUri(), 'index' => $index]);
        if (false !== $index) {
            unset($this->processing[$index]);
        }
        if (null !== $process) {
            $index = array_search($process, $this->processes, true);
            if (false !== $index) {
                unset($this->processes[$index]);
            }
        }
        $this->logger->debug(
            'Number of pending processed {processed}, processing {processing}',
            ['processed' => count($this->processes), 'processing' => count($this->processing)]
        );
    }

    /**
     * @param  RequestInterface  $request
     * @param  SpiderInterface  $spider
     * @return PromiseInterface
     */
    private function process(RequestInterface $request, SpiderInterface $spider)
    {
        $this->logger->debug('Process request {uri}', ['uri' => $request->getUri()]);

        $this->processing[] = $request;

        $process = null;

        try {
            $processor = $this->requestProcessorFactory->factory($request);

            $process = $processor->process($this, $request, $spider);

            $this->processes[] = $process;

            return $process->run()->always(function () use ($request, $process) {
                $this->requestProcessFinished($request, $process);
            });
        } catch (Exception $e) {
            $this->requestProcessFinished($request, $process);
            return reject($e);
        }
    }

    private function scheduleCrawl(RequestInterface $request, SpiderInterface $spider)
    {
        $this->logger->debug('Scheduling request {uri}', ['uri' => (string) $request->getUri()]);

        return $this->schedule($request, $spider)->then(function ($scheduled) {
            if ($scheduled) {
                $this->next->schedule();
            }
            return true;
        });
    }

    public function crawl(RequestInterface $request, CrawlerInterface $crawler)
    {
        /*
        if (null !== $this->dupeFilter) {
            return $this->dupeFilter->isDuplicateRequest($request)->then(function ($isDuplicate) use (
                $request,
                $spider
            ) {
                if ($isDuplicate) {
                    $this->logger->debug('Request {uri} is a duplicate request', ['uri' => $request->getUri()]);
                    throw new DropRequestException('Duplicate request');
                } else {
                    $this->logger->debug('Request {uri} is not a duplicate request', ['uri' => $request->getUri()]);
                }
                return $this->scheduleCrawl($request, $spider);
            });
        }
        return $this->scheduleCrawl($request, $spider);
        */
        $this->schedule($request, $crawler);
        $this->next->schedule();
    }

    private function spiderIsIdle(SpiderInterface $spider)
    {
        $this->logger->debug('Check if spider idle');
        if (!$this->scraper->isIdle()) {
            //$this->logger->debug("Not idle (scraper is not idle)");
            return false;
        }
        if (!empty($this->processing)) {
            //$this->logger->debug("Not idle (still processing requests)");
            //$this->logger->debug('this url ' . reset($this->processing)->getUri());
            return false;
        }

        if ($this->startRequestIndex < count($this->startRequests)) {
            //$this->logger->debug("Not idle (pending start requests)");
            return false;
        }
        if ($this->scheduler->hasPendingRequests()) {
            //$this->logger->debug("Not idle (pending scheduled requests)");
            return false;
        }

        $this->logger->debug('Spider is idle');

        return true;
    }

    private function spiderIdle(SpiderInterface $spider)
    {
        $this->logger->debug('Spider became idle');
    }

    private function nextRequest(SpiderInterface $spider)
    {
        if ($this->paused) {
            return;
        }

        if (!$this->needsBackOut($spider)) {
            return $this->nextRequestFromScheduler($spider)->then(null, function () use ($spider) {
                // Fail to get next request
                if (!empty($this->startRequests) && !$this->needsBackOut($spider)) {
                    if (false !== ($request = current($this->startRequests))) {
                        $this->crawl($request, $spider)->then(null, function ($e) {
                            $this->logger->error('Error during crawl: {error}', ['error' => $e->getMessage()]);
                            $this->stop();
                        });
                        // Move pointer to the next request
                        next($this->startRequests);
                        $this->startRequestIndex++;
                    }
                    $this->next->schedule(1);
                }

                return true;
            })->then(function () use ($spider) {
                if ($this->spiderIsIdle($spider)) {
                    $this->maybeShutdownIdleSpider($spider);
                }
            }, function ($e) {
                $this->logger->error($e->getMessage());
            });
        }
    }

    private function closeSpider(SpiderInterface $spider, $reason = 'cancelled')
    {
        $this->logger->info('Close spider');

        if ($this->closing) {
            return $this->closing;
        }

        $deferred = new Deferred();
        $promise = $deferred->promise();

        $promise->then(function () {
            // Cancel the next call
            $this->next->cancel();
            return true;
        });

        $promise->then(function () {
            $this->heartbeat->stop();
        });

        $promise->then(function () use ($spider) {
            return $this->scraper->closeSpider($spider);
        });

        $promise->then(function () use ($spider, $reason) {
            return $this->scheduler->close($spider, $reason);
        });

        $promise->then(function ($result) use ($spider) {
            $this->events->dispatch(new SpiderClosed($spider));
        });

        $promise->then(function () {
            $this->closeWait->resolve(true);
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
                throw new \Exception('Already running');
            }
            $this->startTime = date_create();
            $this->events->dispatch(new EngineStartedEvent($this));
            $this->logger->info('Started');
            $this->running = true;
            $this->closeWait = new Deferred();
            return $this->closeWait->promise();
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            return new RejectedPromise($e);
        }
    }

    public function stop()
    {
        if (!$this->running) {
            throw new \Exception('Not running');
        }
        $this->running = false;
    }
}
