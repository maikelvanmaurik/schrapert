<?php
namespace Schrapert;

function str_studly($value)
{
    $value = ucwords(str_replace(['-', '_'], ' ', $value));
    return str_replace(' ', '', $value);
}

function str_camel($value)
{
    return lcfirst(str_studly($value));
}