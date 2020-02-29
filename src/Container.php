<?php

namespace Bgdnp\FotonDI;

use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    protected $pool;
    protected $resolver;
    protected $parameters;

    public function __construct()
    {
        $this->pool = new Pool();
        $this->resolver = new Resolver($this->pool);

        $this->pool->add(static::class, $this);
        $this->pool->add(ContainerInterface::class, $this);
    }

    public function get($key)
    {
        if ($this->pool->has($key)) {
            return $this->pool->get($key);
        }

        $parameters = $this->parameters;
        $this->parameters = null;

        return $this->resolver->resolve($key, $parameters);
    }

    public function invoke(string $class, string $method)
    {
        $instance = $this->get($class);

        return $this->resolver->invoke($instance, $method);
    }

    public function has($key)
    {
        return $this->pool->has($key);
    }

    public function parameters(array $parameters): Container
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function build(array $definitions): Container
    {
        foreach ($definitions as $key => $definition) {
            $this->pool->add($key, $definition);
        }

        return $this;
    }
}
