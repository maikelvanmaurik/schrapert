<?php

namespace Schrapert\Http;

use Schrapert\Downloading\RequestInterface as CrawlRequest;
use Psr\Http\Message\RequestInterface as PsrRequest;

interface RequestInterface extends CrawlRequest, PsrRequest
{
}