<?php

namespace Tcds\Io\Jackson\Node;

interface TypeNodeFactory
{
    public function create(string $type): TypeNode;
}
