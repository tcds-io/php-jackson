<?php

namespace Tcds\Io\Serializer\Metadata;

use BackedEnum;
use ReflectionClass;
use Tcds\Io\Serializer\Exception\SerializerException;
use Tcds\Io\Serializer\Metadata\Parser\Annotation;
use Tcds\Io\Serializer\Metadata\Parser\ClassAnnotation;
use Tcds\Io\Serializer\Metadata\Parser\ClassParams;
use Tcds\Io\Serializer\Metadata\Parser\Type;

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
     * @param array<ParamName, InputNode> $inputs
     * @param list<ParamName, OutputNode> $outputs
     */
    public function __construct(
        public string $type,
        public array $inputs = [],
        public array $outputs = [],
    ) {
    }

    public static function from(string $type): self
    {
        [$type, $generics] = Annotation::extractGenerics($type);
        $key = generic($type, $generics);

        return self::$nodes[$key] ??= match (true) {
            Type::isScalar($type),
            Type::isEnum($type) => new self($type),
            Type::isList($type, $generics) => new self(
                type: generic($type, $generics),
                inputs: ['value' => new InputNode('value', node: TypeNode::from($generics[0]))],
            ),
            Type::isShapeType($type) => run(function () use ($type) {
                [$type, $params] = Annotation::shaped($type);

                return new self(
                    type: shape($type, $params),
                    inputs: mapOf($params)
                        ->map(fn(string $name, string $type) => [
                            $name,
                            InputNode::from($name, $type),
                        ])
                        ->entries(),
                    outputs: mapOf($params)
                        ->map(fn(string $name, string $type) => [
                            $name,
                            OutputNode::from($name, $type),
                        ])
                        ->entries(),
                );
            }),
            Type::isArray($type) => run(function () use ($type, $generics): TypeNode {
                $key = $generics[0] ?? 'mixed';
                $value = $generics[1] ?? 'mixed';

                return new self(
                    type: generic('map', [$key, $value]),
                    inputs: [
                        'key' => InputNode::from('key', $key),
                        'value' => InputNode::from('value', $value),
                    ],
                );
            }),
            Type::isClass($type) => self::fromClass($type, $generics),
            default => throw new SerializerException("Cannot handle type `$type`"),
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
            inputs: mapOf($params)
                ->map(function (string $paramName, string $paramType) use ($templates) {
                    $paramType = $templates[$paramType] ?? $paramType;
                    [$paramType, $paramGenerics] = Annotation::extractGenerics($paramType);

                    foreach ($paramGenerics as $index => $paramGeneric) {
                        $paramGenerics[$index] = $templates[$index] ?? $templates[$paramGeneric] ?? $paramGeneric;
                    }

                    return [
                        $paramName,
                        InputNode::from($paramName, generic($paramType, $paramGenerics)),
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
        return Type::isScalar($this->mainType());
    }

    public function isEnum(): bool
    {
        return Type::isEnum($this->mainType());
    }

    public function isClass(): bool
    {
        return Type::isResolvedType($this->mainType());
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
            $this->isList() => generic('list', $this->inputs['value']->node->specification()),
            $this->isClass() => run(function () {
                self::$specifications[$this->type] = true;

                return array_map(fn(InputNode $node) => $node->node->specification(), $this->inputs);
            }),
            $this->isArrayMap() => [
                $this->inputs['key']->node->type => $this->inputs['value']->node->specification(),
            ],
            default => throw new SerializerException(sprintf('Unable to load specification of type `%s`', $this->type)),
        };
    }
}
