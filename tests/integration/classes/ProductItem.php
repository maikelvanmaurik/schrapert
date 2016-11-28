<?php
namespace Schrapert\Test\Integration;

use Schrapert\Scraping\Field;
use Schrapert\Scraping\ItemInterface;

class ProductItem implements ItemInterface
{
    public function __construct($name, $price)
    {
        $this->name = new Field('name', $name);
        $this->price = new Field('price', floatval($price));
    }

    public function getFields()
    {
        return [$this->name, $this->price];
    }
}