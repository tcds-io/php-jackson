<?php

namespace Tcds\Io\Jackson\Node;

interface TypeNodeSpecificationFactory
{
    public function create(TypeNode|string $node): mixed;
}
