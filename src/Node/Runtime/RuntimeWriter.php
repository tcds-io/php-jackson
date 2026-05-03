<?php

namespace Tcds\Io\Jackson\Node\Runtime;

use BackedEnum;
use Exception;
use JsonSerializable;
use Override;
use Tcds\Io\Jackson\Node\OutputNode;
use Tcds\Io\Jackson\Node\TypeNode;
use Tcds\Io\Jackson\Node\TypeNodeFactory;
use Tcds\Io\Jackson\Node\Writer;
use Tcds\Io\Jackson\ObjectMapper;

/**
 * @implements Writer<mixed>
 */
readonly class RuntimeWriter implements Writer
{
    public function __construct(
        private TypeNodeFactory $node = new RuntimeTypeNodeFactory(),
    ) {
    }

    #[Override]
    public function __invoke(mixed $data, string $type, ObjectMapper $mapper, array $path): mixed
    {
        return match (true) {
            is_scalar($data) => $data,
            $data instanceof BackedEnum => $data->value,
            is_array($data) => $this->writeArray($data, $mapper, $path),
            is_object($data) => $this->writeFromNode(
                data: $data,
                node: $this->node->create($type),
                mapper: $mapper,
                path: $path,
            ),
            is_null($data) => null,
            default => throw new Exception(sprintf('Unable to write `%s` value', gettype($data))),
        };
    }

    /**
     * @param array<mixed> $data
     * @param list<string> $path
     * @return array<mixed>
     */
    private function writeArray(array $data, ObjectMapper $mapper, array $path): array
    {
        $values = [];

        foreach ($data as $key => $item) {
            $values[$key] = $mapper->writeValue($item, path: [...$path, sprintf('[%s]', $key)]);
        }

        return $values;
    }

    /**
     * @param list<string> $path
     */
    private function writeFromNode(mixed $data, TypeNode $node, ObjectMapper $mapper, array $path = []): mixed
    {
        $isRootObject = empty($path);

        return match (true) {
            $data instanceof JsonSerializable => $data->jsonSerialize(),
            !$isRootObject && $node->isValueObject() => $this->writeFromOutput($data, $node->outputs[0], $mapper, $path),
            default => listOf(...$node->outputs)
                ->indexedBy(fn (OutputNode $output) => $output->name)
                ->mapValues(fn (OutputNode $output) => $this->writeFromOutput($data, $output, $mapper, $path))
                ->entries()
        };
    }

    /**
     * @param list<string> $path
     */
    private function writeFromOutput(mixed $data, OutputNode $output, ObjectMapper $mapper, array $path = []): mixed
    {
        return $mapper->writeValue(
            value: $output->read($data),
            type: $output->type,
            path: [...$path, $output->name],
        );
    }
}
