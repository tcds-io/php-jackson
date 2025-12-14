<?php

namespace Tcds\Io\Jackson\Node\Runtime;

use Override;
use Tcds\Io\Generic\Reflection\ReflectionClass;
use Tcds\Io\Generic\Reflection\ReflectionMethodParameter;
use Tcds\Io\Generic\Reflection\ReflectionProperty;
use Tcds\Io\Generic\Reflection\Type\Parser\TypeParser;
use Tcds\Io\Generic\Reflection\Type\ReflectionType;
use Tcds\Io\Jackson\Exception\JacksonException;
use Tcds\Io\Jackson\Node\InputNode;
use Tcds\Io\Jackson\Node\OutputNode;
use Tcds\Io\Jackson\Node\TypeNode;
use Tcds\Io\Jackson\Node\TypeNodeFactory;

class RuntimeTypeNodeFactory implements TypeNodeFactory
{
    /** @var array<string, TypeNode> */
    public static array $nodes = [];

    #[Override] public function create(string $type): TypeNode
    {
        return self::$nodes[$type] ??= match (true) {
            ReflectionType::isPrimitive($type),
            ReflectionType::isEnum($type) => new TypeNode($type),
            ReflectionType::isList($type) => self::fromGeneric($type),
            ReflectionType::isShape($type) => self::fromShape($type),
            ReflectionType::isArray($type) => self::fromArray($type),
            default => self::fromClass($type),
        };
    }

    private static function fromGeneric(string $type): TypeNode
    {
        [, $generics] = TypeParser::getGenericTypes($type);

        return new TypeNode(
            type: $type,
            inputs: [new InputNode(name: 'value', type: $generics[0])],
        );
    }

    private static function fromShape(string $type): TypeNode
    {
        [$shapeType, $params] = TypeParser::getParamMapFromShape($type);

        return new TypeNode(
            type: $type,
            inputs: mapOf($params)
                ->map(fn ($name, $type) => [$name, new InputNode(name: $name, type: $type)])
                ->values(),
            outputs: mapOf($params)
                ->map(fn ($name, $type) => [
                    $name,
                    $shapeType === 'array'
                        ? OutputNode::param(name: $name, type: $type)
                        : OutputNode::property(name: $name, type: $type),
                ])
                ->values(),
        );
    }

    private static function fromArray(string $type): TypeNode
    {
        [, $generics] = TypeParser::getGenericTypes($type);
        $key = $generics[0] ?? 'mixed';
        $value = $generics[1] ?? 'mixed';

        return new TypeNode(
            type: generic('map', $generics),
            inputs: [
                new InputNode(name: 'key', type: $key),
                new InputNode(name: 'value', type: $value),
            ],
        );
    }

    private static function fromClass(string $type): TypeNode
    {
        $reflection = new ReflectionClass($type);

        return new TypeNode(
            type: generic($reflection->name, $reflection->generics),
            inputs: listOf(...$reflection->getConstructor()->getParameters())
                ->map(fn (ReflectionMethodParameter $param) => new InputNode(
                    name: $param->name,
                    type: $param->getType()->getName(),
                ))
                ->items(),
            outputs: listOf(...self::getAllProperties($reflection))
                ->map(function (ReflectionProperty $property) {
                    $nameOnMethods = ucfirst($property->name);

                    return match (true) {
                        $property->isPublic() => OutputNode::property(
                            name: $property->name,
                            type: $property->getType()->getName(),
                        ),
                        $property->reflection->hasMethod($property->name) => OutputNode::method(
                            name: $property->name,
                            accessor: $property->name,
                            type: $property->getType()->getName(),
                        ),
                        $property->reflection->hasMethod("get$nameOnMethods") => OutputNode::method(
                            name: $property->name,
                            accessor: "get$nameOnMethods",
                            type: $property->getType()->getName(),
                        ),
                        $property->reflection->hasMethod("is$nameOnMethods") => OutputNode::method(
                            name: $property->name,
                            accessor: "is$nameOnMethods",
                            type: $property->getType()->getName(),
                        ),
                        $property->reflection->hasMethod("has$nameOnMethods") => OutputNode::method(
                            name: $property->name,
                            accessor: "has$nameOnMethods",
                            type: $property->getType()->getName(),
                        ),
                        default => throw new JacksonException("Cannot identify property `$property->name` accessor"),
                    };
                })
                ->items(),
        );
    }

    /**
     * @return list<ReflectionProperty>
     */
    private static function getAllProperties(?ReflectionClass $reflection): array
    {
        if (null === $reflection) {
            return [];
        }

        $parentProperties = self::getAllProperties($reflection->getParentClass());
        $classProperties = $reflection->getProperties();

        return array_merge($parentProperties, $classProperties);
    }
}
