<?php
namespace Schrapert\Http;

interface ResponseReaderFactoryInterface
{
    /**
     * @param ResponseInterface $response
     * @return ResponseReaderInterface
     */
    public function factory(ResponseInterface $response);
}