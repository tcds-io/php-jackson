<?php

namespace Tcds\Io\Serializer\Node;

readonly class OutputNode
{
    public function __construct(public string $name, public TypeNode $node, public WriteNodeType $type)
    {
    }

    public function read(mixed $data): mixed
    {
        return match ($this->type) {
            WriteNodeType::PROPERTY => $data->{$this->name},
            WriteNodeType::METHOD => $data->{$this->name}(),
        };
    }
}
