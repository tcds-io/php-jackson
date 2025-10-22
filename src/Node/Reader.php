<?php

namespace Tcds\Io\Serializer\Node;

use Tcds\Io\Serializer\ObjectMapper;

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
