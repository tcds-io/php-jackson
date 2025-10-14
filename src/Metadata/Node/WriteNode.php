<?php

namespace Tcds\Io\Serializer\Metadata\Node;

use ReflectionNamedType;
use ReflectionProperty;
use Tcds\Io\Serializer\Metadata\Reflection;
use Tcds\Io\Serializer\Metadata\TypeNode;
use Tcds\Io\Serializer\Metadata\TypeResolver;

readonly class WriteNode
{
    public function __construct(
        public string $name,
        public TypeNode $node,
        public WriteNodeType $type,
    ) {
    }

    public function read(mixed $data): mixed
    {
        return match ($this->type) {
            WriteNodeType::PROPERTY => $data->{$this->name},
            WriteNodeType::METHOD => $data->{$this->name}(),
        };
    }

    /**
     * @template T
     * @param class-string<T> $type
     * @return list<ReadNode>
     */
    public static function of(string $type): array
    {
        [$type, $templates] = TypeResolver::from($type);
        $reflection = Reflection::of(class: $type);

        return listOf($reflection->getProperties())
            ->filter(fn(ReflectionProperty $property) => $property->isPublic())
            ->map(fn(ReflectionProperty $property) => new self (
                name: $property->name,
                node: TypeNode::lazy($property->getType()),
                type: WriteNodeType::PROPERTY,
            ))
            ->filter()
            ->items();
    }

    private function findPropertyGetter(ReflectionProperty $property): ?self
    {
        $class = $property->getDeclaringClass();
        $getterName = ucfirst($property->name);

        $method = match (true) {
            $class->hasMethod($property->name) => $class->getMethod($property->name),
            $class->hasMethod("get$getterName") => $class->getMethod("get$getterName"),
            $class->hasMethod("is$getterName") => $class->getMethod("is$getterName"),
            $class->hasMethod("has$getterName") => $class->getMethod("has$getterName"),
            default => null,
        };

        if (!$method) {
            return null;
        }

        $type = listOf($method->getReturnType()?->getTypes())
            ->map(fn(ReflectionNamedType $type) => $type->getName())
            ->join('|');

        return new self ($property->name, TypeNode::lazy($type), WriteNodeType::METHOD);
    }
}
