<?php

namespace Tcds\Io\Serializer\Node\Runtime;

use BackedEnum;
use Exception;
use stdClass;
use Tcds\Io\Serializer\Node\OutputNode;
use Tcds\Io\Serializer\Node\TypeNode;
use Tcds\Io\Serializer\Node\TypeNodeFactory;
use Tcds\Io\Serializer\Node\Writer;
use Tcds\Io\Serializer\ObjectMapper;

readonly class RuntimeWriter implements Writer
{
    public function __construct(
        private TypeNodeFactory $node = new RuntimeTypeNodeFactory(),
    ) {
    }

    public function __invoke(mixed $data, string $type, ObjectMapper $mapper)
    {
        return match (true) {
            is_scalar($data) => run(function () use ($data) {
                return $data;
            }),
            $data instanceof BackedEnum => run(function () use ($data) {
                return $data->value;
            }),
            is_array($data) => run(function () use ($type, $mapper, $data) {
                return array_map(fn($item) => $mapper->writeValue($item), $data);
            }),
            $data instanceof stdClass => run(function () use ($type, $mapper, $data) {
                $node = $this->node->create($type);

                return $this->writeFromNode(data: $data, node: $node, mapper: $mapper);
            }),

            is_object($data) => run(function () use ($data, $mapper) {
                $node = $this->node->create($data::class);

                return $this->writeFromNode(data: $data, node: $node, mapper: $mapper);
            }),
            is_null($data) => null,
            default => throw new Exception(sprintf('Unable to write `%s` valur', gettype($data))),
        };
    }

    private function writeFromNode(mixed $data, TypeNode $node, ObjectMapper $mapper): array
    {
        return listOf($node->outputs)
            ->indexedBy(fn(OutputNode $node) => $node->name)
            ->mapValues(function (OutputNode $node) use ($mapper, $data) {
                return $mapper->writeValue($node->read($data), $node->type);
            })
            ->entries();
    }
}
