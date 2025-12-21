<?php

namespace Tcds\Io\Jackson\Node;

use Tcds\Io\Jackson\ObjectMapper;

/**
 * @template T
 */
interface StaticWriter
{
    /**
     * @param T|null $data
     * @param list<string> $trace
     */
    public static function write(mixed $data, string $type, ObjectMapper $mapper, array $trace): mixed;
}
