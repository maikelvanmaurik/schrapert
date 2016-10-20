<?php
namespace Schrapert\Http;

interface __ClientInterface
{
    /**
     * @param $method
     * @param $uri
     * @param array $options
     * @return RequestInterface
     */
    public function request($method, $uri, array $options = []);
}