<?php

namespace Tcds\Io\Serializer\Node;

final class TypeNode
{
    /**
     * @param string $type
     * @param list<InputNode> $inputs
     * @param list<OutputNode> $outputs
     */
    public function __construct(
        public string $type,
        public array $inputs = [],
        public array $outputs = [],
    ) {
    }
}
