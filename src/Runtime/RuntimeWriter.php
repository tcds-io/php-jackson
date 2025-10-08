<?php

namespace Tcds\Io\Serializer\Runtime;

use BackedEnum;
use Tcds\Io\Serializer\Metadata\TypeNodeRepository;
use Tcds\Io\Serializer\Metadata\Writer;

readonly class RuntimeWriter implements Writer
{
    public function __construct(
        private TypeNodeRepository $node = new RuntimeTypeNodeRepository(),
    ) {
    }

    public function __invoke(mixed $data)
    {
        return match (true) {
            is_scalar($data) => run(function () use ($data) {
                return $data;
            }),
            is_array($data) => run(function () use ($data) {
                return array_map(fn($item) => $this->writeFromObject($item), $data);
            }),
            is_a($data, BackedEnum::class) => run(function () use ($data) {
                return $data->value;
            }),
            is_object($data) => run(function () use ($data) {
                return $this->writeFromObject($data);
            }),
        };
    }

    private function writeFromObject(object $data): array
    {
        $node = $this->node->of($data::class);

        return mapOf($node->outputs)
            ->mapKeys(fn($key) => $data->{$key})
            ->entries();
    }
}
