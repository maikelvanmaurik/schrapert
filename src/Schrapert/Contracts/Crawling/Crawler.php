<?php

namespace Schrapert\Contracts\Crawling;

interface Crawler
{
    public function parse(Response $response);
}
