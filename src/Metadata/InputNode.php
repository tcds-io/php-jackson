<?php

namespace Tcds\Io\Serializer\Metadata;

readonly class InputNode
{
    public function __construct(
        public string $name,
        public TypeNode $node,
    ) {
    }

    public function __toString(): string
    {
        return "$this->node";
    }

    public static function from(string $name, string $type): self
    {
        return lazyOf(self::class, fn() => new self($name, TypeNode::from($type)));
    }
}
