<?php

namespace Schrapert\Http;

interface ReadableResponseStreamInterface
{
    public function pause();

    public function resume();

    public function on($event, callable $callback);
}
