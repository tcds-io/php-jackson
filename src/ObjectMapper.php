<?php

namespace Tcds\Io\Serializer;

use Tcds\Io\Serializer\Mapper\Reader;

/**
 * @phpstan-type MapperType string|class-string<mixed>
 * @phpstan-type ReaderFn Reader::__invoke
 */
abstract readonly class ObjectMapper
{
    /**
     * @template T
     * @param array<MapperType, array{
     *     reader: Reader|ReaderFn,
     * }> $typeMappers
     */
    public function __construct(
        private Reader $defaultTypeReader,
        private array $typeMappers = [],
    ) {
    }

    /**
     * @template T
     * @return T
     */
    abstract public function readValueWith(string $type, string $value, array $with = []);

    /**
     * @template T
     * @return T
     */
    final public function readValue(string $type, mixed $data, array $trace = [])
    {
        $reader = $this->typeMappers[$type]['reader'] ?? $this->defaultTypeReader;

        return $reader($data, $this, $type, $trace);
    }
}
