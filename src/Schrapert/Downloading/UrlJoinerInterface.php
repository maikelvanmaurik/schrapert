<?php

namespace Schrapert\Downloading;

interface UrlJoinerInterface
{
    public function join($uri, $baseUri);
}
