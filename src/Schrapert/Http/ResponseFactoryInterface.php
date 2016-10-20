<?php
namespace Schrapert\Http;

interface ResponseFactoryInterface
{
    public function createResponse($version, $code, $reasonPhrase, $headers, $body);
}