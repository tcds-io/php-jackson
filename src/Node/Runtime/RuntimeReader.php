<?php

namespace Tcds\Io\Serializer\Node\Runtime;

use BackedEnum;
use Override;
use Tcds\Io\Generic\Reflection\Type\ReflectionType;
use Tcds\Io\Generic\Reflection\Type\TypeParser;
use Tcds\Io\Serializer\Exception\SerializerException;
use Tcds\Io\Serializer\Exception\UnableToParseValue;
use Tcds\Io\Serializer\Node\InputNode;
use Tcds\Io\Serializer\Node\Reader;
use Tcds\Io\Serializer\Node\TypeNode;
use Tcds\Io\Serializer\Node\TypeNodeFactory;
use Tcds\Io\Serializer\Node\TypeNodeSpecificationFactory;
use Tcds\Io\Serializer\ObjectMapper;
use Throwable;
use TypeError;

readonly class RuntimeReader implements Reader
{
    public function __construct(
        private TypeNodeFactory $node = new RuntimeTypeNodeFactory(),
        private TypeNodeSpecificationFactory $specification = new RuntimeTypeNodeSpecificationFactory(),
    ) {
    }

    #[Override] public function __invoke(mixed $data, string $type, ObjectMapper $mapper, array $trace)
    {
        $node = $this->node->create($type);

        return match (true) {
            $node->type === 'bool' || $node->type === 'boolean' => filter_var($data, FILTER_VALIDATE_BOOL),
            ReflectionType::isPrimitive($node->type) => $data,
            ReflectionType::isList($node->type) => $this->readList($mapper, $node, $data, $trace),
            ReflectionType::isEnum($node->type) => $this->readEnum($node->type, $data),
            ReflectionType::isShape($node->type) => $this->readShape($mapper, $node, $data, $trace),
            ReflectionType::isArray($node->type) => $this->readArrayMap($mapper, $node, $data, $trace),
            ReflectionType::isGeneric($node->type),
            ReflectionType::isClass($node->type) => $this->readClass($mapper, $node, $data, $trace),
            default => throw new SerializerException(sprintf('Unable to handle value of type <%s>', $node->type)),
        };
    }

    private function readList(ObjectMapper $mapper, TypeNode $node, mixed $data, array $trace): array
    {
        return array_map(
            callback: function (mixed $item) use ($mapper, $node, $trace) {
                return $mapper->readValue(
                    type: $node->inputs[0]->type,
                    value: $item,
                    trace: $trace,
                );
            },
            array: $data,
        );
    }

    /**
     * @template E of BackedEnum
     * @param class-string<E> $enum
     * @return E
     */
    private function readEnum(string $enum, mixed $value): BackedEnum
    {
        return $enum::from($value);
    }

    /**
     * @template C
     * @return C
     */
    private function readClass(ObjectMapper $mapper, TypeNode $node, mixed $data, array $trace)
    {
        $values = $this->readValues($mapper, $node, $data, $trace);
        [$class] = TypeParser::getGenericTypes($node->type);

        try {
            return new $class(...$values);
        } catch (Throwable $e) {
            throw new UnableToParseValue($trace, $this->specification->create($node->type), $data, $e);
        }
    }

    private function readArrayMap(ObjectMapper $mapper, TypeNode $node, mixed $data, array $trace): array
    {
        $param = $node->inputs[1]->type;

        return array_map(
            callback: fn($item) => $mapper->readValue(
                type: $param,
                value: $item,
                trace: $trace,
            ),
            array: $data,
        );
    }

    private function readShape(ObjectMapper $mapper, TypeNode $node, mixed $data, array $trace): object|array
    {
        $values = $this->readValues($mapper, $node, $data, $trace);

        return str_starts_with($node->type, 'array')
            ? $values
            : (object) $values;
    }

    private function readValueObject(ObjectMapper $mapper, InputNode $param, mixed $data, array $trace): array
    {
        $data = $data[$param->name] ?? $data;

        return [
            $param->name => $mapper->readValue($param->type, $data, $trace),
        ];
    }

    private function readValues(ObjectMapper $mapper, TypeNode $node, mixed $data, array $trace): array
    {
        $values = [];

        if (count($node->inputs) === 1) {
            $param = array_values($node->inputs)[0];

            return $this->readValueObject($mapper, $param, $data, $trace);
        }

        foreach ($node->inputs as $input) {
            $value = $data[$input->name] ?? null;
            $innerTrace = [...$trace, $input->name];

            try {
                $values[$input->name] = $mapper->readValue($input->type, $value, $innerTrace);
            } catch (TypeError $e) {
                $node = $this->node->create($input->type);

                throw new UnableToParseValue($innerTrace, $this->specification->create($node->type), $value, $e);
            }
        }

        return $values;
    }
}
