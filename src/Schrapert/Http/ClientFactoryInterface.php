<?php
namespace Schrapert\Http;

interface __ClientFactoryInterface
{
    /**
     * @return ClientInterface
     */
    public function factory();
}