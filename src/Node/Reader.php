<?php

namespace Tcds\Io\Serializer\Node;

use Tcds\Io\Serializer\ArrayObjectMapper;

interface Reader
{
    /**
     * @template T
     * @param list<string> $trace
     * @return T
     */
    public function __invoke(mixed $data, ArrayObjectMapper $mapper, string $type, array $trace);
}
