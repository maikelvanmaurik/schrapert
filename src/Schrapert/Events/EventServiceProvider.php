<?php
namespace Schrapert\Events;

use Schrapert\Foundation\Application;

class EventServiceProvider
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function register()
    {
        $this->app->singleton(\Schrapert\Contracts\Events\Dispatcher::class, function() {
           return new Dispatcher;
        });
    }
}