<?php

namespace Tcds\Io\Serializer\Node;

use Tcds\Io\Serializer\ObjectMapper;

/**
 * @template T
 */
interface Writer
{
    /**
     * @param T|null $data
     */
    public function __invoke(mixed $data, string $type, ObjectMapper $mapper): mixed;
}
