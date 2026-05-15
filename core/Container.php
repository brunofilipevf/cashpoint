<?php

namespace Core;

use ReflectionClass;

class Container
{
    private $instances = [];

    public function get($class)
    {
        if (isset($this->instances[$class])) {
            return $this->instances[$class];
        }

        $reflection = new ReflectionClass($class);
        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return $this->instances[$class] = new $class();
        }

        $dependencies = array_map(
            fn($param) => $this->get($param->getType()->getName()),
            $constructor->getParameters()
        );

        return $this->instances[$class] = $reflection->newInstanceArgs($dependencies);
    }
}
