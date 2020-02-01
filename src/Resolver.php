<?php

namespace Bgdnp\Foton\DI;

use ReflectionClass;
use ReflectionParameter;

class Resolver
{
    protected $pool;

    public function __construct(Pool $pool)
    {
        $this->pool = $pool;
    }

    public function resolve(string $key)
    {
        return $this->resolveClass(new ReflectionClass($key));
    }

    protected function resolveClass(ReflectionClass $reflection)
    {
        $key = $reflection->getName();

        if ($this->pool->has($key)) {
            return $this->pool->get($key);
        }

        $constructor = $reflection->getConstructor();

        if (!$constructor) {
            return $this->newInstance($reflection);
        }

        $dependencies = [];

        foreach ($constructor->getParameters() as $parameter) {
            $dependencies[$parameter->getPosition()] = $this->resolveParameter($parameter);
        }

        return $this->newInstance($reflection, $dependencies);
    }

    protected function resolveParameter(ReflectionParameter $parameter)
    {
        if ($parameter->getClass()) {
            return $this->resolveClass($parameter->getClass());
        }

        return null;
    }

    protected function newInstance(ReflectionClass $reflection, array $dependencies = null, bool $save = true)
    {
        if ($dependencies) {
            $instance = $reflection->newInstanceArgs($dependencies);
        } else {
            $instance = $reflection->newInstance();
        }

        if ($save) {
            $this->pool->add($reflection->getName(), $instance);
        }

        return $instance;
    }
}
