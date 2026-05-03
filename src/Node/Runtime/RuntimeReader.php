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

    #[Override]
    public function __invoke(mixed $data, string $type, ObjectMapper $mapper, array $path): mixed
    {
        $node = $this->node->create($type);

        return match (true) {
            is_null($data) => null,
            $node->type === 'bool' || $node->type === 'boolean' => $this->readBool($data, $path),
            ReflectionType::isPrimitive($node->type) => $data,
            ReflectionType::isList($node->type) => $this->readList($mapper, $node, $data, $path),
            ReflectionType::isEnum($node->type) => $this->readEnum($node->type, $data, $path),
            ReflectionType::isShape($node->type) => $this->readShape($mapper, $node, $data, $path),
            ReflectionType::isArray($node->type) => $this->readArrayMap($mapper, $node, $data, $path),
            ReflectionType::isGeneric($node->type),
            ReflectionType::isClass($node->type) => $this->readClass($mapper, $node, $data, $path),
            default => throw new JacksonException(sprintf('Unable to handle value of type <%s>', $node->type), $path),
        };
    }

    /**
     * @param list<string> $path
     * @return array<mixed>
     */
    private function readList(ObjectMapper $mapper, TypeNode $node, mixed $data, array $path): array
    {
        $type = asClassString($node->inputs[0]->type);
        $values = [];

        foreach (asArray($data) as $index => $item) {
            $values[$index] = $mapper->readValue(
                type: $type,
                value: $item,
                path: [...$path, sprintf('[%s]', $index)],
            );
        }

        return $values;
    }

    /**
     * @param list<string> $path
     */
    private function readBool(mixed $data, array $path): bool
    {
        $result = filter_var($data, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);

        return $result ?? throw new UnableToParseValue($path, 'bool', $data);
    }

    /**
     * @template E of BackedEnum
     * @param class-string<E> $enum
     * @param list<string> $path
     * @return E
     */
    private function readEnum(string $enum, mixed $value, array $path): BackedEnum
    {
        try {
            return $enum::from(asStringOrInt($value));
        } catch (Throwable $e) {
            throw new UnableToParseValue($path, $this->specification->create($enum), $value, $e);
        }
    }

    /**
     * @param list<string> $path
     */
    private function readClass(ObjectMapper $mapper, TypeNode $node, mixed $data, array $path): mixed
    {
        $values = $this->readValues($mapper, $node, $data, $path);
        [$class] = TypeParser::getGenericTypes($node->type);

        try {
            return new $class(...$values);
        } catch (Throwable $e) {
            throw new UnableToParseValue($path, $this->specification->create($node->type), $data, $e);
        }
    }

    /**
     * @param list<string> $path
     * @return array<mixed>
     */
    private function readArrayMap(ObjectMapper $mapper, TypeNode $node, mixed $data, array $path): array
    {
        $type = asClassString($node->inputs[1]->type);
        $values = [];

        foreach (asArray($data) as $key => $item) {
            $values[$key] = $mapper->readValue(
                type: $type,
                value: $item,
                path: [...$path, (string) $key],
            );
        }

        return $values;
    }

    /**
     * @param list<string> $path
     * @return object|array<mixed>
     */
    private function readShape(ObjectMapper $mapper, TypeNode $node, mixed $data, array $path): object|array
    {
        $values = $this->readValues($mapper, $node, asArray($data), $path);

        return str_starts_with($node->type, 'array')
            ? $values
            : (object) $values;
    }

    /**
     * @param list<string> $path
     * @return array<mixed>
     */
    private function readValueObject(ObjectMapper $mapper, InputNode $param, mixed $data, array $path): array
    {
        $data = is_array($data) && array_key_exists($param->name, $data)
            ? $data[$param->name]
            : $data;

        return [
            $param->name => $mapper->readValue(asClassString($param->type), $data, $path),
        ];
    }

    /**
     * @param list<string> $path
     * @return array<mixed>
     */
    private function readValues(ObjectMapper $mapper, TypeNode $node, mixed $data, array $path): array
    {
        $values = [];

        if (!is_array($data) || count($node->inputs) === 1) {
            $param = $node->inputs[0];

            return $this->readValueObject($mapper, $param, $data, $path);
        }

        foreach ($node->inputs as $input) {
            $value = $data[$input->name] ?? $input->default;
            $innerpath = [...$path, $input->name];

            try {
                $values[$input->name] = $mapper->readValue(asClassString($input->type), $value, $innerpath);
            } catch (TypeError) {
                $inputNode = $this->node->create($input->type);

                throw new UnableToParseValue($innerpath, $this->specification->create($inputNode->type), $value);
            }
        }

        return $values;
    }
}
