<?php

namespace Tcds\Io\Serializer\Fixture;

/**
 * @template K
 * @template V of object
 */
readonly class Pair
{
    /**
     * @param K $key
     * @param V $value
     */
    public function __construct(public mixed $key, public object $value)
    {
    }

    /**
     * @return K
     */
    public function key(): mixed
    {
        return $this->key;
    }

    /**
     * @return V
     */
    public function value(): object
    {
        return $this->value;
    }
}
