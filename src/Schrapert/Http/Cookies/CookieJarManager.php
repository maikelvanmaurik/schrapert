<?php

namespace Schrapert\Http\Cookies;

class CookieJarManager implements CookieJarManagerInterface
{
    private CookieJarFactoryInterface $factory;

    private array $jars;

    private static CookieJarManagerInterface $instance;

    public function __construct(CookieJarFactoryInterface $factory)
    {
        $this->factory = $factory;
        $this->jars = [];
    }

    public static function getInstance() : ?CookieJarManagerInterface
    {
        return self::$instance;
    }

    public static function setInstance(CookieJarManagerInterface $manager)
    {
        self::$instance = $manager;
    }

    public function get(string $name) : CookieJarInterface
    {
        if (! isset($this->jars[$name])) {
            $this->jars[$name] = $this->factory->createCookieJar();
        }
        return $this->jars[$name];
    }

    public function set(string $name, CookieJarInterface $jar) : CookieJarManagerInterface
    {
        $this->jars[$name] = $jar;
        return $this;
    }

    public function remove(string $name) : CookieJarManagerInterface
    {
        unset($this->jars[$name]);
        return $this;
    }

    public function offsetExists($offset)
    {
        return isset($this->jars[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }
}
