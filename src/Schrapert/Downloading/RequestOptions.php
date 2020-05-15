<?php
namespace Schrapert\Downloading;

class RequestOptions implements RequestOptionsInterface
{
    private array $options;

    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    public function get($key, $default = null)
    {
         return $this->has($key) ? $this->options[$key] : $default;
    }

    public function set($key, $value)
    {
        $this->options[$key] = $value;
        return $this;
    }

    public function has($key): bool
    {
        return array_key_exists($key, $this->options);
    }
}
