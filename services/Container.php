<?php

namespace Services;

class Container
{
    private static $container;

    private $dependencies = [];
    private $instances = [];
    private $reflections = [];

    public static function setContainer($container)
    {
        self::$container = $container;
    }

    public static function get($class)
    {
        return self::$container->resolve($class);
    }

    public function resolve($class)
    {
        if (isset($this->instances[$class])) {
            return $this->instances[$class];
        }

        if (!isset($this->reflections[$class])) {
            $this->reflections[$class] = new \ReflectionClass($class);
        }

        $reflection = $this->reflections[$class];
        $constructor = $reflection->getConstructor();

        if (!$constructor) {
            return $this->instances[$class] = new $class;
        }

        if (!isset($this->dependencies[$class])) {
            $deps = [];

            foreach ($constructor->getParameters() as $parameter) {
                $deps[] = $parameter->getType()->getName();
            }

            $this->dependencies[$class] = $deps;
        }

        $resolved = [];

        foreach ($this->dependencies[$class] as $dependency) {
            $resolved[] = $this->resolve($dependency);
        }

        return $this->instances[$class] = $reflection->newInstanceArgs($resolved);
    }
}
