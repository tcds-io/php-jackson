<?php

namespace Tcds\Io\Serializer\Metadata;

use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;

readonly class OutputNode
{
    public function __construct(
        public string $name,
        public TypeNode $node,
        public OutputNodeType $type,
    ) {
    }

    public function read(mixed $data): mixed
    {
        return match ($this->type) {
            OutputNodeType::PROPERTY => $data->{$this->name},
            OutputNodeType::METHOD => $data->{$this->name}(),
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
                    ? new self ($property->name, TypeNode::lazy($property->getType()), OutputNodeType::PROPERTY)
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

        return new self ($property->name, TypeNode::lazy($type), OutputNodeType::METHOD);
    }
}
