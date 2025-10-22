<?php

namespace Tcds\Io\Serializer\Node\Runtime;

use BackedEnum;
use Exception;
use Tcds\Io\Serializer\Node\OutputNode;
use Tcds\Io\Serializer\Node\TypeNode;
use Tcds\Io\Serializer\Node\TypeNodeFactory;
use Tcds\Io\Serializer\Node\Writer;
use Tcds\Io\Serializer\ObjectMapper;

/**
 * @implements Writer<mixed>
 */
readonly class RuntimeWriter implements Writer
{
    public function __construct(
        private TypeNodeFactory $node = new RuntimeTypeNodeFactory(),
    ) {
    }

    public function __invoke(mixed $data, string $type, ObjectMapper $mapper): mixed
    {
        return match (true) {
            is_scalar($data) => $data,
            $data instanceof BackedEnum => $data->value,
            is_array($data) => array_map(fn ($item) => $mapper->writeValue($item), $data),
            is_object($data) => $this->writeFromNode(
                data: $data,
                node: $this->node->create($type),
                mapper: $mapper,
            ),
            is_null($data) => null,
            default => throw new Exception(sprintf('Unable to write `%s` valur', gettype($data))),
        };
    }

    /**
     * @return array<mixed>
     */
    private function writeFromNode(mixed $data, TypeNode $node, ObjectMapper $mapper): array
    {
        return listOf(...$node->outputs)
            ->indexedBy(fn (OutputNode $node) => $node->name)
            ->mapValues(function (OutputNode $node) use ($mapper, $data) {
                return $mapper->writeValue($node->read($data), $node->type);
            })
            ->entries();
    }
}
