<?php

namespace Tcds\Io\Serializer\Metadata\Parser;

use BackedEnum;
use ReflectionParameter;
use Tcds\Io\Serializer\Exception\SerializerException;
use Traversable;

class ParamType
{
    public static function of(ReflectionParameter $param): string
    {
        $class = $param->getDeclaringClass() ?: throw new SerializerException('Not a class! Serializer can parse only class params');
        $runtimeTypes = ClassAnnotation::runtimeTypes($class);
        $templates = ClassAnnotation::templates($class);

        $type = Annotation::param(
            function: $param->getDeclaringFunction(),
            name: $param->name,
        ) ?: $param->getType()->getName();

        if (array_key_exists($type, $templates)) {
            return $type;
        }

        if (self::isResolvedType($type)) {
            return $type;
        }

        if (self::isShapeType($type)) {
            [$type, $params] = Annotation::shapedFqn($class, $type);

            return sprintf('%s{ %s }', $type, join(', ', $params));
        }

        if (array_key_exists($type, $runtimeTypes)) {
            $type = $runtimeTypes[$type];
        }

        return Annotation::generic($class, $type);
    }

    public static function isScalar(string $type): bool
    {
        $simpleNodeTypes = ['int', 'float', 'string', 'bool', 'boolean', 'mixed'];
        $types = explode('|', str_replace('&', '|', $type));

        $notScalar = array_filter($types, fn($t) => !in_array($t, $simpleNodeTypes, true));

        if (count($types) > 1 && !empty($notScalar)) {
            throw new SerializerException('Non-scalar union types are not allowed');
        }

        return empty($notScalar);
    }

    public static function isResolvedType(string $type): bool
    {
        return class_exists($type) ||
            enum_exists($type) ||
            self::isScalar($type);
    }

    public static function isShapeType(?string $type): bool
    {
        return str_starts_with($type ?? '', 'array{') || str_starts_with($type ?? '', 'object{');
    }

    /**
     * @param list<string> $generics
     */
    public static function isList(string $type, array $generics): bool
    {
        return ($type === 'list')
            || ($type === 'iterable')
            || ($type === Traversable::class)
            || ($type === 'array' && count($generics) === 1);
    }

    public static function isArray(string $type): bool
    {
        return $type === 'array'
            || $type === 'map';
    }

    /**
     * @phpstan-assert-if-true class-string<BackedEnum> $type
     */
    public static function isEnum(string $type): bool
    {
        return enum_exists($type);
    }

    public static function isClass(string $type): bool
    {
        return class_exists($type);
    }
}
