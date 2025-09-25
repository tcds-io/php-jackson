<?php

namespace Tcds\Io\Serializer\Mapper;

use Tcds\Io\Serializer\ObjectMapper;

interface Reader
{
    /**
     * @template T
     * @param list<string> $trace
     * @return T
     */
    public function __invoke(mixed $data, ObjectMapper $mapper, string $type, array $trace);
}
