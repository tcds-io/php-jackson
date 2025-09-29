<?php

namespace Tcds\Io\Serializer\Metadata;

readonly class ParamNode
{
    public function __construct(public TypeNode $node)
    {
    }

    public function __toString(): string
    {
        return "$this->node";
    }

    public static function from(string $type): self
    {
        return lazyOf(self::class, fn () => new self(TypeNode::from($type)));
    }
}
