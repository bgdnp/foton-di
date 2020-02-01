<?php

namespace Bgdnp\Foton\DI;

use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    protected $pool;
    protected $resolver;

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

        return $this->resolver->resolve($key);
    }

    public function has($key)
    {
        return $this->pool->has($key);
    }
}
