<?php

namespace Schrapert\Downloading\Middleware;

use Schrapert\Downloading\DownloaderInterface;

/**
 * Default implementation of the download middleware factory
 * which allows types to be registered so that custom middleware
 * can be used.
 */
class DownloadMiddlewareFactory implements DownloadMiddlewareFactoryInterface
{
    private $types;

    public function __construct()
    {
        $this->types = [];
    }

    public function register($type, callable $factory)
    {
        $this->types[$type] = $factory;
    }

    public function factory($type, DownloaderInterface $downloader)
    {
        return isset($this->types[$type]) && is_callable($this->types[$type])
            ? call_user_func($this->types[$type], $downloader)
            : null;
    }
}
