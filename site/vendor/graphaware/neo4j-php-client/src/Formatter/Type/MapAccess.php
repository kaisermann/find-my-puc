<?php

namespace GraphAware\Neo4j\Client\Formatter\Type;

use GraphAware\Common\Type\MapAccessor;

class MapAccess implements MapAccessor
{
    /**
     * @var array
     */
    protected $properties = [];

    /**
     * {@inheritdoc}
     */
    public function value($key, $default = null)
    {
        if (!array_key_exists($key, $this->properties) && 1 === func_num_args()) {
            throw new \InvalidArgumentException(sprintf('this object has no property with key %s', $key));
        }

        return array_key_exists($key, $this->properties) ? $this->properties[$key] : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function hasValue($key)
    {
        return array_key_exists($key, $this->properties);
    }

    /**
     * @return array
     */
    public function keys()
    {
        return array_keys($this->properties);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        return $this->value($key);
    }

    /**
     * {@inheritdoc}
     */
    public function containsKey($key)
    {
        return array_key_exists($key, $this->properties);
    }

    /**
     * {@inheritdoc}
     */
    public function values()
    {
        return $this->properties;
    }

    /**
     * {@inheritdoc}
     */
    public function asArray()
    {
        return $this->properties;
    }
}
