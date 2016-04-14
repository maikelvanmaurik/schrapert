<?php
namespace Schrapert\Http;

interface ClientFactoryInterface
{
    /**
     * @return ClientInterface
     */
    public function factory();
}