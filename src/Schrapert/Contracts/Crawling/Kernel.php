<?php

namespace Schrapert\Contracts\Crawling;

interface Kernel
{
    /**
     * @param Crawler|string|array|iterable $crawler
     * @return mixed
     */
    public function crawl($crawler);
}
