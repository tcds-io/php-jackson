<?php

namespace Tcds\Io\Serializer\Metadata;

use Tcds\Io\Serializer\ObjectMapper;

interface Writer
{
    public function __invoke(mixed $data, string $type, ObjectMapper $mapper);
}
