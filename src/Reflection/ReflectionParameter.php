<?php

declare(strict_types=1);

namespace Tcds\Io\Serializer\Reflection;

use Override;
use ReflectionNamedType as OriginalReflectionNamedType;
use ReflectionParameter as OriginalReflectionParameter;
use ReturnTypeWillChange;
use Tcds\Io\Serializer\Reflection\Type\ReflectionType;

class ReflectionParameter extends OriginalReflectionParameter
{
    public ReflectionClass $reflection {
        get => $this->method->reflection;
    }

    public function __construct(
        private readonly ReflectionMethod $method,
        string $param,
    ) {
        parent::__construct(
            [$method->reflection->name, $method->name],
            $param,
        );
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
}
