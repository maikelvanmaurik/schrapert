<?php
namespace Schrapert\Core;

use Schrapert\Filter\DuplicateRequestFilterInterface;
use Schrapert\Log\LoggerInterface;
use Schrapert\Schedule\SchedulerInterface;
use Schrapert\Signal\SignalManager;
use Schrapert\Util\DelayedCallbackFactory;
use Schrapert\Util\IntervalCallbackFactory;

class ExecutionEngineFactory
{
    private $signals;

    private $scraper;

    private $requestProcessorFactory;

    private $scheduler;

    private $delayedCallbackFactory;

    private $intervalCallbackFactory;

    private $dupeFilter;

    private $logger;

    public function __construct(LoggerInterface $logger, SignalManager $signals, ScraperInterface $scraper, DuplicateRequestFilterInterface $dupeFilter, RequestProcessorFactoryInterface $requestProcessorFactory, SchedulerInterface $scheduler, IntervalCallbackFactory $intervalCallbackFactory, DelayedCallbackFactory $delayedCallbackFactory)
    {
        $this->dupeFilter = $dupeFilter;
        $this->scraper = $scraper;
        $this->intervalCallbackFactory = $intervalCallbackFactory;
        $this->delayedCallbackFactory = $delayedCallbackFactory;
        $this->signals = $signals;
        $this->requestProcessorFactory = $requestProcessorFactory;
        $this->scheduler = $scheduler;
        $this->logger = $logger;
    }

    public function factory()
    {
        return new ExecutionEngine($this->logger, $this->signals, $this->scraper, $this->dupeFilter, $this->requestProcessorFactory, $this->scheduler, $this->intervalCallbackFactory, $this->delayedCallbackFactory);
    }
}