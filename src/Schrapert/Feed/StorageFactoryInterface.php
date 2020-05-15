<?php

namespace Schrapert\Feed;

interface StorageFactoryInterface
{
    /**
     * @param $uri
     * @return StorageInterface
     */
    public function createStorage($uri);
}
