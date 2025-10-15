<?php

declare(strict_types=1);

namespace Tcds\Io\Serializer\Reflection;

use Override;
use ReflectionMethod as OriginalReflectionMethod;
use ReflectionNamedType as OriginalReflectionNamedType;
use ReflectionParameter as OriginalReflectionParameter;
use ReturnTypeWillChange;
use Tcds\Io\Serializer\Reflection\Type\ReflectionType;

class ReflectionMethod extends OriginalReflectionMethod
{
    public function __construct(public readonly ReflectionClass $reflection, ?string $method = null)
    {
        parent::__construct($reflection->name, $method);
    }

    #[Override] public function getParameters(): array
    {
        return array_map(
            fn(OriginalReflectionParameter $param) => new ReflectionParameter($this, $param->name),
            parent::getParameters(),
        );
    }

    #[ReturnTypeWillChange]
    #[Override] public function getReturnType(): ReflectionType
    {
        return ReflectionType::create($this);
    }

    public function getOriginalReturnType(): OriginalReflectionNamedType
    {
        $type = parent::getReturnType();

        return $type instanceof OriginalReflectionNamedType
            ? $type
            : new OriginalReflectionNamedType();
    }
}
