<?php

namespace Schrapert\Crawling;

use Schrapert\Contracts\Crawling\Kernel as KernelContract;
use Schrapert\Contracts\Events\Dispatcher;
use Schrapert\Contracts\Foundation\Application;

class Kernel implements KernelContract
{
    protected $app;

    protected $events;

    protected $engine;

    /**
     * The bootstrap classes for the application.
     *
     * @var array
     */
    protected $bootstrappers = [
        \Schrapert\Foundation\Bootstrap\LoadEnvironmentVariables::class,
        \Schrapert\Foundation\Bootstrap\LoadConfiguration::class,
        \Schrapert\Foundation\Bootstrap\HandleExceptions::class,
        \Schrapert\Foundation\Bootstrap\SetRequestForConsole::class,
        \Schrapert\Foundation\Bootstrap\RegisterProviders::class,
        \Schrapert\Foundation\Bootstrap\BootProviders::class,
    ];

    public function __construct(Application $app, Dispatcher $events, Engine $engine)
    {
        $this->app = $app;
        $this->events = $events;
        $this->engine = $engine;
    }

    public function crawl($crawler)
    {
        try {
            $crawlers = func_get_args() > 1
                ? func_get_args()
                : (is_iterable($crawler) ? iterator_to_array($crawler) : [$crawler]);

            $crawlers = array_map(function ($crawler) {
                return $this->app->make($crawler);
            }, $crawlers);

            $this->engine->run($crawlers);
        } catch (\Throwable $e) {
        }
    }

    protected function bootstrap()
    {
        if (! $this->app->hasBeenBootstrapped()) {
            $this->app->bootstrapWith($this->bootstrappers());
        }
    }

    /**
     * Get the bootstrap classes for the application.
     *
     * @return array
     */
    protected function bootstrappers()
    {
        return $this->bootstrappers;
    }
}
