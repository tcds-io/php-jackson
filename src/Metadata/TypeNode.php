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
    /** @var array<string, self> */
    public static array $nodes = [];
    /** @var array<string, mixed> */
    public static array $specifications = [];

    /**
     * @param string $type
     * @param array<ParamName, ParamNode> $params
     */
    public function __construct(
        public string $type,
        public array $params = [],
    ) {
    }

    public static function from(string $type): self
    {
        [$type, $generics] = Annotation::extractGenerics($type);
        $key = generic($type, $generics);

        return self::$nodes[$key] ??= match (true) {
            ParamType::isScalar($type),
            ParamType::isEnum($type) => run(function () use ($type): TypeNode {
                return new TypeNode($type);
            }),
            ParamType::isList($type, $generics) => run(function () use ($type, $generics): TypeNode {
                return new self(
                    type: generic($type, $generics),
                    params: [
                        'value' => new ParamNode('value', node: TypeNode::from($generics[0])),
                    ],
                );
            }),
            ParamType::isShapeType($type) => run(function () use ($type) {
                [$type, $params] = Annotation::shaped($type);

                return new TypeNode(
                    type: shape($type, $params),
                    params: mapOf($params)
                        ->map(fn(string $paramName, string $paramType) => [
                            $paramName,
                            ParamNode::from($paramName, $paramType),
                        ])
                        ->entries(),
                );
            }),
            ParamType::isArray($type) => run(function () use ($type, $generics): TypeNode {
                $key = $generics[0] ?? 'mixed';
                $value = $generics[1] ?? 'mixed';

                return new TypeNode(
                    type: generic('map', [$key, $value]),
                    params: [
                        'key' => ParamNode::from('key', $key),
                        'value' => ParamNode::from('value', $value),
                    ],
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
            params: mapOf($params)
                ->map(function (string $paramName, string $paramType) use ($templates) {
                    $paramType = $templates[$paramType] ?? $paramType;
                    [$paramType, $paramGenerics] = Annotation::extractGenerics($paramType);

                    foreach ($paramGenerics as $index => $paramGeneric) {
                        $paramGenerics[$index] = $templates[$index] ?? $templates[$paramGeneric] ?? $paramGeneric;
                    }

                    return [
                        $paramName,
                        ParamNode::from($paramName, generic($paramType, $paramGenerics)),
                    ];
                })
                ->entries(),
        );
    }

    public function mainType(): string
    {
        [$main] = Annotation::extractGenerics($this->type);

        return $main;
    }

    public function isBoolean(): bool
    {
        return $this->type === 'bool'
            || $this->type === 'boolean';
    }

    public function isScalar(): bool
    {
        return ParamType::isScalar($this->mainType());
    }

    public function isEnum(): bool
    {
        return ParamType::isEnum($this->mainType());
    }

    public function isClass(): bool
    {
        return ParamType::isResolvedType($this->mainType());
    }

    public function isList(): bool
    {
        return $this->mainType() === 'list';
    }

    public function isArrayMap(): bool
    {
        return $this->mainType() === 'map';
    }

    public function isShapeValue(): bool
    {
        return str_starts_with($this->type, 'array{')
            || str_starts_with($this->type, 'object{');
    }

    public function specification(): array|string
    {
        if (array_key_exists($this->type, self::$specifications)) {
            return [];
        }

        return match (true) {
            $this->isScalar() => $this->type,
            $this->isEnum() => array_map(fn(BackedEnum $enum) => $enum->value, $this->type::cases()),
            $this->isList() => generic('list', $this->params['value']->node->specification()),
            $this->isClass() => run(function () {
                self::$specifications[$this->type] = true;

                return array_map(fn(ParamNode $node) => $node->node->specification(), $this->params);
            }),
            $this->isArrayMap() => [
                $this->params['key']->node->type => $this->params['value']->node->specification(),
            ],
            default => throw new SerializerException(sprintf('Unable to load specification of type `%s`', $this->type)),
        };
    }
}
