<?php
namespace Schrapert\Configuration;

use Traversable;

class Configuration implements ConfigurationInterface
{
    private $settings;

    public function __construct(array $settings = null)
    {
        if(null !== $settings) {
            $this->setSettings($settings);
        }
    }

    public function getSetting($key, $default = null)
    {
        return array_key_exists($key, $this->settings) ? $this->settings[$key] : $default;
    }

    public function setSetting($key, $value)
    {
        $this->settings[$key] = $value;
    }

    public function setSettings($settings)
    {
        if(!is_array($settings) && !$settings instanceof Traversable) {
            throw new \InvalidArgumentException("Invalid settings given");
        }
        foreach($settings as $k => $v) {
            $this->setSetting($k, $v);
        }
    }
}