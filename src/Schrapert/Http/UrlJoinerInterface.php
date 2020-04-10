<?php

namespace Schrapert\Http;

interface UrlJoinerInterface
{
    public function join($uri, $baseUri);
}
