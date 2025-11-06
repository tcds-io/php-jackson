<?php

namespace Tcds\Io\Jackson\Node\Runtime;

use BackedEnum;
use Override;
use Tcds\Io\Generic\Reflection\Type\Parser\TypeParser;
use Tcds\Io\Generic\Reflection\Type\ReflectionType;
use Tcds\Io\Jackson\Exception\JacksonException;
use Tcds\Io\Jackson\Exception\UnableToParseValue;
use Tcds\Io\Jackson\Node\InputNode;
use Tcds\Io\Jackson\Node\Reader;
use Tcds\Io\Jackson\Node\TypeNode;
use Tcds\Io\Jackson\Node\TypeNodeFactory;
use Tcds\Io\Jackson\Node\TypeNodeSpecificationFactory;
use Tcds\Io\Jackson\ObjectMapper;
use Throwable;
use TypeError;

/**
 * @implements Reader<mixed>
 */
readonly class RuntimeReader implements Reader
{
    public function __construct(
        private TypeNodeFactory $node = new RuntimeTypeNodeFactory(),
        private TypeNodeSpecificationFactory $specification = new RuntimeTypeNodeSpecificationFactory(),
    ) {
    }

    #[Override] public function __invoke(mixed $data, string $type, ObjectMapper $mapper, array $trace): mixed
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
            default => throw new JacksonException(sprintf('Unable to handle value of type <%s>', $node->type)),
        };
    }

    /**
     * @param list<string> $trace
     * @return array<mixed>
     */
    private function readList(ObjectMapper $mapper, TypeNode $node, mixed $data, array $trace): array
    {
        return array_map(
            callback: function (mixed $item) use ($mapper, $node, $trace) {
                return $mapper->readValue(
                    type: asClassString($node->inputs[0]->type),
                    value: $item,
                    trace: $trace,
                );
            },
            array: asArray($data),
        );
    }

    /**
     * @template E of BackedEnum
     * @param class-string<E> $enum
     * @return E
     */
    private function readEnum(string $enum, mixed $value): BackedEnum
    {
        return $enum::from(asStringOrInt($value));
    }

    /**
     * @param list<string> $trace
     */
    private function readClass(ObjectMapper $mapper, TypeNode $node, mixed $data, array $trace): mixed
    {
        $values = $this->readValues($mapper, $node, $data, $trace);
        [$class] = TypeParser::getGenericTypes($node->type);

        try {
            return new $class(...$values);
        } catch (Throwable $e) {
            throw new UnableToParseValue($trace, $this->specification->create($node->type), $data, $e);
        }
    }

    /**
     * @param list<string> $trace
     * @return array<mixed>
     */
    private function readArrayMap(ObjectMapper $mapper, TypeNode $node, mixed $data, array $trace): array
    {
        return array_map(
            callback: fn ($item) => $mapper->readValue(
                type: asClassString($node->inputs[1]->type),
                value: $item,
                trace: $trace,
            ),
            array: asArray($data),
        );
    }

    /**
     * @param list<string> $trace
     * @return object|array<mixed>
     */
    private function readShape(ObjectMapper $mapper, TypeNode $node, mixed $data, array $trace): object|array
    {
        $values = $this->readValues($mapper, $node, asArray($data), $trace);

        return str_starts_with($node->type, 'array')
            ? $values
            : (object) $values;
    }

    /**
     * @param list<string> $trace
     * @return array<mixed>
     */
    private function readValueObject(ObjectMapper $mapper, InputNode $param, mixed $data, array $trace): array
    {
        $data = is_array($data) && array_key_exists($param->name, $data)
            ? $data[$param->name]
            : $data;

        return [
            $param->name => $mapper->readValue(asClassString($param->type), $data, $trace),
        ];
    }

    /**
     * @param list<string> $trace
     * @return array<mixed>
     */
    private function readValues(ObjectMapper $mapper, TypeNode $node, mixed $data, array $trace): array
    {
        $values = [];

        if (!is_array($data) || count($node->inputs) === 1) {
            $param = $node->inputs[0];

            return $this->readValueObject($mapper, $param, $data, $trace);
        }

        foreach ($node->inputs as $input) {
            $value = $data[$input->name] ?? null;
            $innerTrace = [...$trace, $input->name];

            try {
                $values[$input->name] = $mapper->readValue(asClassString($input->type), $value, $innerTrace);
            } catch (TypeError $e) {
                $node = $this->node->create($input->type);

                throw new UnableToParseValue($innerTrace, $this->specification->create($node->type), $value);
            }
        }

        return $values;
    }
}
