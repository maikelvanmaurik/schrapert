<?php
namespace Schrapert\Feed;

class StorageFactory implements StorageFactoryInterface
{
    private $types;

    public function __construct()
    {
        $this->types = [];
        $this->registerDefaults();
    }

    public function register($type, callable $callback)
    {
        $this->types[$type] = $callback;
    }

    private function registerDefaults()
    {
        $this->register('file', function($uri) {
            return new FileStorage($uri);
        });
        $this->register('ftp', function($uri) {
            return new FtpStorage();
        });
    }

    /**
     * @param $uri
     * @return StorageInterface
     */
    public function createStorage($uri)
    {
        $scheme = parse_url($uri, PHP_URL_SCHEME);
        if(!array_key_exists($scheme, $this->types)) {
            throw new \RuntimeException(sprintf("Unknown storage type '%s'", $scheme));
        }

        return call_user_func($this->types[$scheme], $uri);
    }
}