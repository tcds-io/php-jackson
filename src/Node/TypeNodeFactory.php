<?php

namespace Tcds\Io\Serializer\Node;

interface TypeNodeFactory
{
    public function create(string $type): TypeNode;
}
