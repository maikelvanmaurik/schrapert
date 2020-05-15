<?php

namespace Schrapert\Tests\Support;

use Schrapert\DI\Container;
use Schrapert\Downloading\Downloader;
use Schrapert\DI\DefaultContainer;
use Schrapert\Downloading\HandlerFactory;
use Schrapert\Downloading\HandlerFactoryInterface;
use Schrapert\Events\EventDispatcher;
use Schrapert\Events\EventDispatcherInterface;
use Schrapert\Pipeline\PipelineBuilder;
use Schrapert\Pipeline\PipelineBuilderInterface;

trait CreatesDownloader
{
    protected function createDownloader(
        ?EventDispatcherInterface $events,
        ?PipelineBuilderInterface $pipelineBuilder,
        ?HandlerFactoryInterface $handlerFactory,
        array $middleware = []
    ) {
        if (null === $events) {
            $events = new EventDispatcher();
        }
        if (null === $pipelineBuilder) {
            $pipelineBuilder = new PipelineBuilder(Container::getInstance());
        }
        if (null === $handlerFactory) {
            $handlerFactory = new HandlerFactory(new DefaultContainer());
        }
        return new Downloader($events, $pipelineBuilder, $handlerFactory, $middleware);
    }
}
