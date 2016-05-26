<?php
namespace Schrapert\Http;

use React\Promise\PromiseInterface;

interface ResponseReaderInterface
{
    /**
     * @return PromiseInterface
     */
    public function readToEnd();
}