<?php

namespace Tcds\Io\Jackson\Node;

interface TypeNodeSpecificationFactory
{
    /**
     * @return array<string, mixed>|list<mixed>|string
     */
    public function create(TypeNode|string $node): array|string;
}
