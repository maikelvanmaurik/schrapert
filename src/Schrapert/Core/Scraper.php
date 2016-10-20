<?php
namespace Schrapert\Core;

use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use Schrapert\Crawl\RequestInterface;
use Schrapert\Crawl\ResponseInterface;
use Schrapert\Log\LoggerInterface;
use Schrapert\Scraping\Item;
use Schrapert\SpiderInterface;
use Traversable;
use Generator;
use Exception;

class Scraper implements ScraperInterface
{
    private $queue;

    private $loop;

    private $logger;

    private $active;

    private $timer;

    public function __construct(LoggerInterface $logger, LoopInterface $loop)
    {
        $this->queue = [];
        $this->active = [];
        $this->logger = $logger;
        $this->loop = $loop;
    }

    private function finishScraping(RequestInterface $request)
    {
        $index = array_search($request, $this->active, true);
        $this->logger->debug("Finished scraping of request %s, index: %s", [$request->getUri(), $index]);
        if(false !== $index) {
            unset($this->active[$index]);
        }
        foreach($this->queue as $index => $queue) {
            list($engine, $req, $res, $deferred) = $queue;
            if($req === $request) {
                unset($this->queue[$index]);
            }
        }
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param SpiderInterface $spider
     * @return PromiseInterface
     */
    public function enqueueScrape(ExecutionEngine $engine, RequestInterface $request, ResponseInterface $response, SpiderInterface $spider)
    {
        $this->logger->debug("Enqueue scrape %s", [$request->getUri()]);
        $deferred = new Deferred();
        $this->queue[] = [$engine, $request, $response, $deferred];

        // Adding to the scrape queue is done in memory so we can resolve directly
        $deferred->resolve(true);

        $promise = $deferred->promise();

        /*
        $promise->then(function() use ($request) {
            $this->finishScraping($request);
        });
        */
        return $promise;
    }

    public function isIdle()
    {
        $isIdle = empty($this->queue) && empty($this->active);

        $this->logger->debug("Scraper is%sidle", [$isIdle ? ' ' : ' not ']);

        return $isIdle;
    }

    public function closeSpider(SpiderInterface $spider)
    {
        if($this->timer) {
            $this->loop->cancelTimer($this->timer);
        }
    }

    public function open(SpiderInterface $spider)
    {
        if($this->timer) {
            return;
        }
        $this->timer = $this->loop->addPeriodicTimer(1, function() use ($spider) {
            $this->next($spider);
        });
    }

    private function callSpider(SpiderInterface $spider, RequestInterface $request, ResponseInterface $response)
    {
        $deferred = new Deferred();
        $this->loop->futureTick(function() use ($deferred, $request, $spider, $response) {
            $this->logger->debug("Call spider");
            $this->active[] = $request;
            $result = call_user_func(is_callable($request->getCallback()) ? $request->getCallback() : array($spider, 'parse'), $response);
            if($result instanceof PromiseInterface) {
                $result->then(function($item) use ($deferred) {
                    $deferred->resolve($item);
                });
            } else {
                $deferred->resolve($result);
            }
        });
        return $deferred->promise();
    }

    private function handleItem($item, ExecutionEngine $engine, SpiderInterface $spider)
    {
        if($item instanceof RequestInterface) {
            return $engine->crawl($item, $spider);
        } elseif($item instanceof Item) {
            return $this->itemProcessor->process($item);
        }
    }

    private function handleSpiderOutput($output, ExecutionEngine $engine, SpiderInterface $spider)
    {
        $deferred = new Deferred();
        $this->loop->futureTick(function() use ($output, $engine, $spider, $deferred) {

            if($output instanceof Generator || $output instanceof Traversable) {
                foreach($output as $item) {
                    try {
                        $this->handleItem($item, $engine, $spider);
                    } catch(StopIterationException $e) {
                        break;
                    } catch(Exception $e) {
                        $deferred->reject($e);
                    }
                }
            } else {
                $this->handleItem($output, $engine, $spider);
            }

            $deferred->resolve(true);
        });
        return $deferred->promise();
    }

    private function handleSpiderError()
    {

    }

    private function next(SpiderInterface $spider)
    {
        $this->logger->debug("Scrape next");
        if(null !== ($data = array_pop($this->queue))) {
            list($engine, $request, $response, $deferred) = $data;

            $this->logger->debug("Scrape %s", [$request->getUri()]);

            return $this->callSpider($spider, $request, $response)
                ->then(
                    function($output) use ($spider, $engine) {
                        return $this->handleSpiderOutput($output, $engine, $spider);
                    }, function($error) use ($spider, $engine) {
                        return $this->handleSpiderError($error, $engine, $spider);
                    }
                )->then(
                    function($output) use ($deferred) {
                        return $deferred->resolve($output);
                    }, function($error) use ($deferred) {
                        return $deferred->reject($error);
                    }
                )->always(function() use ($request) {
                    return $this->finishScraping($request);
                });
        }
    }
}