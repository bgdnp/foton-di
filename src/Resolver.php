<?php

namespace Bgdnp\Foton\DI;

use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;

class Resolver
{
    protected $pool;
    protected $parameters;
    protected $saveToPool = true;

    public function __construct(Pool $pool)
    {
        $this->pool = $pool;
    }

    public function resolve(string $key, ?array $parameters)
    {
        $this->parameters = $parameters;

        return $this->resolveClass(new ReflectionClass($key));
    }

    public function invoke($instance, string $method)
    {
        $reflection = new ReflectionMethod($instance, $method);

        $parameters = [];

        foreach ($reflection->getParameters() as $parameter) {
            $parameters[] = $this->resolveParameter($parameter);
        }

        return $reflection->invokeArgs($instance, $parameters);
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

        $parameters = [];

        foreach ($constructor->getParameters() as $parameter) {
            $parameters[] = $this->resolveParameter($parameter);
        }

        return $this->newInstance($reflection, $parameters);
    }

    protected function resolveParameter(ReflectionParameter $parameter)
    {
        if ($this->parameters[$parameter->getName()]) {
            $this->saveToPool = false;

            return $this->parameters[$parameter->getName()];
        }

        if ($parameter->getClass()) {
            return $this->resolveClass($parameter->getClass());
        }

        if ($parameter->isDefaultValueAvailable()) {
            $this->saveToPool = false;

            return $parameter->getDefaultValue();
        }

        return null;
    }

    protected function newInstance(ReflectionClass $reflection, array $dependencies = null)
    {
        if ($dependencies) {
            $instance = $reflection->newInstanceArgs($dependencies);
        } else {
            $instance = $reflection->newInstance();
        }

        if ($this->saveToPool) {
            $this->pool->add($reflection->getName(), $instance);
        }

        $this->saveToPool = true;

        if (method_exists($instance, '__init')) {
            $this->invoke($instance, '__init');
        }

        return $instance;
    }
}
