<?php
namespace Schrapert\Scraping;

interface ItemInterface
{
    /**
     * @return FieldInterface[]
     */
    public function getFields();
}