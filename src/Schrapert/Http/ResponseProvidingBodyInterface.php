<?php
namespace Schrapert\Http;

/**
 * Represents a fully downloaded or created response with the full body
 */
interface ResponseProvidingBodyInterface extends ResponseInterface
{
    /**
     * @return string
     */
    public function getBody();
}