<?php

namespace Tcds\Io\Jackson\Node;

use Tcds\Io\Jackson\ObjectMapper;

/**
 * @template T
 */
interface StaticReader
{
    /**
     * @param list<string> $trace
     * @return T|null
     */
    public static function read(mixed $data, string $type, ObjectMapper $mapper, array $trace);
}
