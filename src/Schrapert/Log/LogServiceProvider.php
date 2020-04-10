<?php
namespace Schrapert\Log;

class LogServiceProvider
{
    public function __construct($app)
    {
        $this->app = $app;
    }

    public function register()
    {
        $this->app->singleont
    }
}