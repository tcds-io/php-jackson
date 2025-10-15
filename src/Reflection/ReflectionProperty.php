<?php

declare(strict_types=1);

namespace Tcds\Io\Serializer\Reflection;

use Override;
use ReflectionMethod as OriginalReflectionMethod;
use ReflectionNamedType as OriginalReflectionNamedType;
use ReflectionProperty as OriginalReflectionProperty;
use ReturnTypeWillChange;
use Tcds\Io\Serializer\Exception\SerializerException;
use Tcds\Io\Serializer\Reflection\Type\ReflectionType;

class ReflectionProperty extends OriginalReflectionProperty
{
    public function __construct(public readonly ReflectionClass $reflection, string $property)
    {
        parent::__construct($reflection->name, $property);
    }

    #[ReturnTypeWillChange]
    #[Override] public function getType(): ?ReflectionType
    {
        return ReflectionType::create($this);
    }

    public function getOriginalType(): ?OriginalReflectionNamedType
    {
        $type = parent::getType();

        return $type instanceof OriginalReflectionNamedType
            ? $type
            : new OriginalReflectionNamedType();
    }

    public function getConstructor(): OriginalReflectionMethod
    {
        return $this->reflection->getConstructor() ?: throw new SerializerException("Class `$this->reflection->name` has no constructor");
    }
}
