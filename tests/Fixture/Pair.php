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
    public function __construct(public mixed $key, object $value)
    {
    }
}
