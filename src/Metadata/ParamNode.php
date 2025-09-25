<?php

namespace Tcds\Io\Serializer\Metadata;

readonly class ParamNode
{
    public function __construct(public TypeNode $type)
    {
    }

    public function __toString(): string
    {
        return "$this->type";
    }

    /**
     * @param list<string> $generics
     */
    public static function from(string $type, array $generics = []): self
    {
        return new self(TypeNode::from($type, $generics));
    }
}
