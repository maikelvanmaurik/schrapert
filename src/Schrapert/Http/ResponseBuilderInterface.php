<?php
namespace Schrapert\Http;

interface ResponseBuilderInterface
{
    public function setBody($body);

    public function setHeaders($headers);

    /**
     * @return ResponseInterface
     */
    public function build();
}