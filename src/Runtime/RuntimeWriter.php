<?php

namespace Tcds\Io\Serializer\Runtime;

use Tcds\Io\Serializer\Metadata\Node\WriteNode;
use Tcds\Io\Serializer\Metadata\Parser\Type;
use Tcds\Io\Serializer\Metadata\TypeNode;
use Tcds\Io\Serializer\Metadata\TypeNodeRepository;
use Tcds\Io\Serializer\Metadata\Writer;
use Tcds\Io\Serializer\ObjectMapper;

readonly class RuntimeWriter implements Writer
{
    public function __construct(
        private TypeNodeRepository $node = new RuntimeTypeNodeRepository(),
    ) {
    }

    public function __invoke(mixed $data, string $type, ObjectMapper $mapper)
    {
        return match (true) {
            is_scalar($data) => run(function () use ($data) {
                return $data;
            }),
            Type::isEnum($data) => run(function () use ($data) {
                return $data->value;
            }),
            is_array($data) => run(function () use ($type, $mapper, $data) {
                return array_map(fn($item) => $this->writeFromNode(
                    data: $item,
                    node: $this->node->of($type),
                    mapper: $mapper,
                ), $data);
            }),
            is_object($data) => run(function () use ($data, $mapper) {
                return $this->writeFromNode(
                    data: $data,
                    node: $this->node->of($data::class),
                    mapper: $mapper,
                );
            }),
        };
    }

    private function writeFromNode(mixed $data, TypeNode $node, ObjectMapper $mapper): array
    {
        return listOf($node->outputs)
            ->indexedBy(fn(WriteNode $node) => $node->name)
            ->mapValues(function (WriteNode $node) use ($mapper, $data) {
                return $mapper->writeValue($node->read($data), $node->node->type);
            })
            ->entries();
    }
}
