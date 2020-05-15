<?php

namespace Schrapert\Http;

use Psr\Http\Message\ResponseInterface as PsrResponse;
use Schrapert\Downloading\ResponseInterface as CrawlResponse;

interface ResponseInterface extends CrawlResponse, PsrResponse
{
}
