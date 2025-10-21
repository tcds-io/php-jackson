<?php

namespace Tcds\Io\Serializer\Node;

use Tcds\Io\Serializer\ObjectMapper;

interface Writer
{
    public function __invoke(mixed $data, string $type, ObjectMapper $mapper): mixed;
}
