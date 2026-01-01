<?php

namespace Tcds\Io\Jackson\Node;

use Tcds\Io\Jackson\ObjectMapper;

/**
 * @template T
 */
interface Writer
{
    /**
     * @param T|null $data
     * @param list<string> $path
     */
    public function __invoke(mixed $data, string $type, ObjectMapper $mapper, array $path): mixed;
}
