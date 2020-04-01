<?php
namespace Schrapert\Scraping;

class Field implements FieldInterface
{
    private $name;

    private $value;

    public function __construct($name, $value = null)
    {
        $this->name = $name;
        $this->value = $value;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getValue()
    {
        return $this->value;
    }
}