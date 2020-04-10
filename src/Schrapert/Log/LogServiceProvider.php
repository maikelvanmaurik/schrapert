<?php
namespace Schrapert\Log;

use Schrapert\Foundation\Application;

class LogServiceProvider
{
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function register()
    {
    }
}