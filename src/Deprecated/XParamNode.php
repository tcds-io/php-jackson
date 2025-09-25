<?php

namespace Tcds\Io\Serializer\Deprecated;

use ReflectionParameter;
use Tcds\Io\Generic\ArrayList;
use Traversable;

/**
 * @deprecated
 */
readonly class XParamNode
{
    /**
     * @param array<XTypeNode> $generics
     */
    public function __construct(
        public string $name,
        public XTypeNode $type,
        public array $generics = [],
    ) {
    }

    public function isTraversable(): bool
    {
        return $this->type->type === 'list'
            || $this->type->type === Traversable::class
            || class_implements($this->type->type, Traversable::class);
    }

    /**
     * @param ArrayList<XTypeNode> $templates
     */
    public static function from(
        ReflectionParameter $param,
        ArrayList $templates = new ArrayList([]),
    ): self {
        $generic = Generic::from($param, $templates);
        $templates = array_map(fn(string $template) => XTypeNode::lazy($template), $generic->templates);

        return new self(
            name: $param->name,
            type: XTypeNode::lazy($generic->resolved, listOf(...$templates)),
            generics: $templates,
        );
    }

    public function describe(): string
    {
        $generics = array_map(fn(XTypeNode $generic) => $generic->type, $this->generics);

        return empty($this->generics)
            ? $this->type->type
            : sprintf("<%s>", join(', ', $generics));
    }
}
