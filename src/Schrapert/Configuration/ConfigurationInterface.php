<?php
namespace Schrapert\Configuration;

interface ConfigurationInterface
{
    public function getSetting($key, $default = null);

    public function setSetting($key, $value);


}