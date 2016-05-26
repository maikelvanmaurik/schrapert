<?php
namespace Schrapert\Http;

class ResponseReaderFactory implements ResponseReaderFactoryInterface
{
    /**
     * @param ResponseInterface $response
     * @return ResponseReaderInterface
     */
    public function factory(ResponseInterface $response)
    {
        return new ResponseReader($response);
    }
}