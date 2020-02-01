<?php

namespace Bgdnp\Foton\DI;

class Pool
{
    protected $pool = [];

    public function get(string $key)
    {
        return $this->pool[$key];
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->pool);
    }

    public function add(string $key, $value): Pool
    {
        $this->pool[$key] = $value;

        return $this;
    }
}
