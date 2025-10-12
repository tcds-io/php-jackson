<?php

namespace Tcds\Io\Serializer\Metadata\Node;

use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use Tcds\Io\Serializer\Metadata\TypeNode;

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
     * @param ReflectionClass $reflection
     * @return list<self>
     */
    public static function fromReflectionClass(ReflectionClass $reflection, array $templates): array
    {
        return listOf($reflection->getProperties())
            ->map(function (ReflectionProperty $property) {
                return $property->isPublic()
                    ? new self ($property->name, TypeNode::lazy($property->getType()), WriteNodeType::PROPERTY)
                    : $this->findPropertyGetter($property);
            })
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
