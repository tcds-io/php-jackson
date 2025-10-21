<?php

namespace Tcds\Io\Serializer\Node;

use Tcds\Io\Serializer\ArrayObjectMapper;

interface Reader
{
    /**
     * @param list<string> $trace
     */
    public function __invoke(mixed $data, string $type, ArrayObjectMapper $mapper, array $trace): mixed;
}
