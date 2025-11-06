<?php

namespace Tcds\Io\Jackson\Node;

use Tcds\Io\Jackson\ObjectMapper;

/**
 * @template T
 */
interface Reader
{
    /**
     * @param list<string> $trace
     * @return T|null
     */
    public function __invoke(mixed $data, string $type, ObjectMapper $mapper, array $trace);
}
