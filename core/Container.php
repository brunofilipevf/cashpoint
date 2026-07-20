<?php

namespace Core;

class Container
{
    private $instances = [];

    public function get($class)
    {
        if (isset($this->instances[$class])) {
            return $this->instances[$class];
        }

        return $this->resolve($class);
    }

    private function resolve($class)
    {
        if (!class_exists($class)) {
            throw new \RuntimeException("[Container] Classe '{$class}' não encontrada");
        }

        $reflection = new \ReflectionClass($class);
        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            $this->instances[$class] = new $class();
            return $this->instances[$class];
        }

        $dependencies = [];

        foreach ($constructor->getParameters() as $parameter) {
            $dependencies[] = $this->get($parameter->getType()->getName());
        }

        $this->instances[$class] = $reflection->newInstanceArgs($dependencies);
        return $this->instances[$class];
    }
}
