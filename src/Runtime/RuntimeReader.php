<?php

namespace Tcds\Io\Serializer\Runtime;

use BackedEnum;
use Override;
use Tcds\Io\Serializer\Exception\SerializerException;
use Tcds\Io\Serializer\Exception\UnableToParseValue;
use Tcds\Io\Serializer\Metadata\InputNode;
use Tcds\Io\Serializer\Metadata\Reader;
use Tcds\Io\Serializer\Metadata\TypeNode;
use Tcds\Io\Serializer\Metadata\TypeNodeRepository;
use Tcds\Io\Serializer\ObjectMapper;
use Throwable;
use TypeError;

readonly class RuntimeReader implements Reader
{
    public function __construct(
        private TypeNodeRepository $node = new RuntimeTypeNodeRepository(),
    ) {
    }

    #[Override] public function __invoke(mixed $data, ObjectMapper $mapper, string $type, array $trace)
    {
        $node = $this->node->of($type);

        return match (true) {
            $node->isBoolean() => filter_var($data, FILTER_VALIDATE_BOOL),
            $node->isScalar() => $data,
            $node->isList() => $this->readList($mapper, $node, $data, $trace),
            $node->isEnum() => $this->readEnum($node->type, $data),
            $node->isClass() => $this->readClass($mapper, $node, $data, $trace),
            $node->isArrayMap() => $this->readArrayMap($mapper, $node, $data, $trace),
            $node->isShapeValue() => $this->readShape($mapper, $node, $data, $trace),
            default => throw new SerializerException(sprintf('Unable to handle value of type <%s>', $node->type)),
        };
    }

    private function readList(ObjectMapper $mapper, TypeNode $node, mixed $data, array $trace): array
    {
        return array_map(
            callback: function (mixed $item) use ($mapper, $node, $trace) {
                return $mapper->readValue(
                    type: $node->inputs['value']->node->type,
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
    private function readEnum(string $enum, mixed $value)
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
        $class = $node->mainType();

        try {
            return new $class(...$values);
        } catch (Throwable $e) {
            throw new UnableToParseValue($trace, $node->specification(), $data, $e);
        }
    }

    private function readArrayMap(ObjectMapper $mapper, TypeNode $node, mixed $data, array $trace)
    {
        $param = $node->inputs['value']->node->type;

        return array_map(
            callback: fn($item) => $mapper->readValue(
                type: $param,
                value: $item,
                trace: $trace,
            ),
            array: $data,
        );
    }

    private function readShape(ObjectMapper $mapper, TypeNode $node, mixed $data, array $trace)
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
            $param->name => $mapper->readValue($param->node->type, $data, $trace),
        ];
    }

    private function readValues(ObjectMapper $mapper, TypeNode $node, mixed $data, array $trace): array
    {
        $values = [];

        if (count($node->inputs) === 1) {
            $param = array_values($node->inputs)[0];

            return $this->readValueObject($mapper, $param, $data, $trace);
        }

        foreach ($node->inputs as $name => $param) {
            $value = $data[$name] ?? null;
            $innerTrace = [...$trace, $name];

            try {
                $values[$name] = $mapper->readValue($param->node->type, $value, $innerTrace);
            } catch (TypeError $e) {
                throw new UnableToParseValue($innerTrace, $param->node->specification(), $value, $e);
            }
        }

        return $values;
    }
}
