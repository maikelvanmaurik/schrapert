<?php
namespace Schrapert\Http;

interface ResponseReaderResultInterface
{
    /**
     * @return ResponseInterface
     */
    public function getResponse();
    /**
     * @return string
     */
    public function getBody();
    /**
     * @return \Exception
     */
    public function getError();

    /**
     * @return mixed
     */
    public function __toString();
}