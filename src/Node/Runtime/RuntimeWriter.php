<?php

namespace Tcds\Io\Jackson\Node\Runtime;

use BackedEnum;
use Exception;
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
    ) {}

    public function __invoke(mixed $data, string $type, ObjectMapper $mapper): mixed
    {
        return match (true) {
            is_scalar($data) => $data,
            $data instanceof BackedEnum => $data->value,
            is_array($data) => array_map(fn($item) => $mapper->writeValue($item), $data),
            is_object($data) => $this->writeFromNode(
                data: $data,
                node: $this->node->create($type),
                mapper: $mapper,
            ),
            is_null($data) => null,
            default => throw new Exception(sprintf('Unable to write `%s` valur', gettype($data))),
        };
    }

    private function writeFromNode(mixed $data, TypeNode $node, ObjectMapper $mapper): mixed
    {
        if ($node->isValueObject()) {
            return $this->writeFromOutput($data, $node->outputs[0], $mapper);
        }

        return listOf(...$node->outputs)
            ->indexedBy(fn(OutputNode $output) => $output->name)
            ->mapValues(fn(OutputNode $output) => $this->writeFromOutput($data, $output, $mapper))
            ->entries();
    }

    private function writeFromOutput(mixed $data, OutputNode $output, ObjectMapper $mapper): mixed
    {
        return $mapper->writeValue(value: $output->read($data), type: $output->type);
    }
}
