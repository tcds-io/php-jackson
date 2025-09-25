<?php

namespace Tcds\Io\Serializer\Metadata;

use BackedEnum;
use ReflectionClass;
use Tcds\Io\Serializer\Exception\SerializerException;
use Tcds\Io\Serializer\Metadata\Parser\Annotation;
use Tcds\Io\Serializer\Metadata\Parser\ClassAnnotation;
use Tcds\Io\Serializer\Metadata\Parser\ClassParams;
use Tcds\Io\Serializer\Metadata\Parser\ParamType;

/**
 * @phpstan-type ParamName string|int
 * @phpstan-type TemplateName string
 */
final class TypeNode
{
    /**
     * @param string $type
     * @param array<ParamName, ParamNode> $params
     */
    public function __construct(
        public string $type,
        public array $params = [],
    ) {
    }

    /**
     * @param list<string> $generics
     */
    public static function from(string $type, array $generics = []): self
    {
        if (empty($generics)) {
            [$type, $generics] = Annotation::extractGenerics($type);
        }

        return match (true) {
            ParamType::isScalar($type),
            ParamType::isEnum($type) => run(function () use ($type): TypeNode {
                return new TypeNode($type);
            }),
            ParamType::isList($type, $generics) => run(function () use ($type, $generics): TypeNode {
                return new self(
                    type: generic($type, $generics),
                    params: [
                        'value' => new ParamNode(type: TypeNode::from($generics[0])),
                    ],
                );
            }),
            ParamType::isShapeType($type) => run(function () use ($type) {
                [$type, $params] = Annotation::shaped($type);

                return new TypeNode(
                    type: shape($type, $params),
                    params: array_map(fn(string $param) => ParamNode::from($param), $params),
                );
            }),
            ParamType::isArray($type) => run(function () use ($type, $generics): TypeNode {
                $key = $generics[0] ?? 'mixed';
                $value = $generics[1] ?? 'mixed';

                return new TypeNode(
                    type: generic('map', [$key, $value]),
                    params: array_map(
                        callback: fn(string $generic) => ParamNode::from($generic),
                        array: ['key' => $key, 'value' => $value],
                    ),
                );
            }),
            ParamType::isClass($type) => run(function () use ($type, $generics): TypeNode {
                return self::fromClass($type, $generics);
            }),
            default => run(function () use ($type) {
                throw new SerializerException("Cannot handle type `$type`");
            }),
        };
    }

    /**
     * @param array<ParamName, string> $generics
     */
    private static function fromClass(string $type, array $generics = []): self
    {
        $reflection = new ReflectionClass($type);
        $params = ClassParams::of(reflection: $reflection);
        $templates = ClassAnnotation::templates(reflection: $reflection);

        foreach (array_keys($templates) as $position => $template) {
            $templates[$template] = $generics[$position] ?? throw new SerializerException("No generic defined for template `$template`");
        }

        return new self(
            type: generic($type, $templates),
            params: array_map(function ($paramType) use ($templates) {
                $paramType = $templates[$paramType] ?? $paramType;
                [$paramType, $paramGenerics] = Annotation::extractGenerics($paramType);

                foreach ($paramGenerics as $index => $paramGeneric) {
                    $paramGenerics[$index] = $templates[$index] ?? $templates[$paramGeneric] ?? $paramGeneric;
                }

                return ParamNode::from($paramType, $paramGenerics);
            }, $params),
        );
    }

    public function isBoolean(): bool
    {
        return $this->type === 'bool'
            || $this->type === 'boolean';
    }

    public function isScalar(): bool
    {
        return ParamType::isScalar($this->type);
    }

    public function isEnum(): bool
    {
        return ParamType::isEnum($this->type);
    }

    public function isClass(): bool
    {
        return ParamType::isResolvedType($this->type);
    }

    public function isList(): bool
    {
        return str_starts_with($this->type, 'list<');
    }

    public function isArrayMap(): bool
    {
        return str_starts_with($this->type, 'map<');
    }

    public function isShapeValue(): bool
    {
        return str_starts_with($this->type, 'array{')
            || str_starts_with($this->type, 'object{');
    }

    public function specification(): array|string
    {
        return match (true) {
            $this->isScalar() => $this->type,
            $this->isEnum() => array_map(fn(BackedEnum $enum) => $enum->value, $this->type::cases()),
            $this->isList() => generic('list', $this->params['value']->type->specification()),
            $this->isClass() => array_map(fn(ParamNode $node) => $node->type->specification(), $this->params),
            default => throw new SerializerException(sprintf('Unable to load specification of type `%s`', $this->type)),
        };
    }
}
