<?php

declare(strict_types=1);

namespace Tcds\Io\Serializer\Reflection\Type;

use ReflectionFunctionAbstract;
use ReflectionType as OriginalReflectionType;
use Tcds\Io\Serializer\Exception\SerializerException;
use Tcds\Io\Serializer\Metadata\Parser\Annotation;
use Tcds\Io\Serializer\Metadata\Parser\Type;
use Tcds\Io\Serializer\Reflection\ReflectionClass;
use Tcds\Io\Serializer\Reflection\ReflectionMethod;
use Tcds\Io\Serializer\Reflection\ReflectionParameter;
use Tcds\Io\Serializer\Reflection\ReflectionProperty;

class ReflectionType extends OriginalReflectionType
{
    public function __construct(public ReflectionClass $reflection, public readonly string $type)
    {
    }

    public function getName(): string
    {
        return $this->type;
    }

    public static function create(
        ReflectionProperty|ReflectionParameter|ReflectionMethod $context,
    ): static {
        $type = match ($context::class) {
            ReflectionProperty::class => self::getTypeForParamOrProperty(
                functionOrMethod: $context->getConstructor(),
                paramOrProperty: $context,
            ),
            ReflectionParameter::class => self::getTypeForParamOrProperty(
                functionOrMethod: $context->getDeclaringFunction(),
                paramOrProperty: $context,
            ),
            ReflectionMethod::class => self::getReturnTypeForMethod(
                method: $context,
            ),
        };

        $reflection = $context->reflection;
        $type = $reflection->aliases[$type] ?? $type;
        $type = $reflection->templates[$type] ?? $type;

        return match (true) {
            class_exists($type) => new ClassReflectionType($reflection, $type),
            enum_exists($type) => new EnumReflectionType($reflection, $type),
            Type::isPrimitive($type) => new PrimitiveReflectionType($reflection, $type),
            Type::isShape($type) => ShapeReflectionType::from($reflection, $type),
            Type::isGeneric($type) => GenericReflectionType::from($reflection, $type),
            default => throw new SerializerException("Unknown type `$type`"),
        };
    }

    private static function getTypeForParamOrProperty(
        ReflectionMethod|ReflectionFunctionAbstract $functionOrMethod,
        ReflectionProperty|ReflectionParameter $paramOrProperty,
    ): string {
        return Annotation::param(
            function: $functionOrMethod,
            name: $paramOrProperty->name,
        ) ?: $paramOrProperty->getOriginalType()->getName();
    }

    private static function getReturnTypeForMethod(
        ReflectionMethod $method,
    ): string {
        return Annotation::return(
            method: $method,
        ) ?: $method->getOriginalReturnType()->getName();
    }
}
