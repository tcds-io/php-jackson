<?php

namespace Tcds\Io\Serializer;

use Override;
use Tcds\Io\Serializer\Metadata\Reader;
use Tcds\Io\Serializer\Metadata\Writer;
use Tcds\Io\Serializer\Runtime\RuntimeReader;
use Tcds\Io\Serializer\Runtime\RuntimeWriter;

/**
 * @phpstan-import-type TypeMapper from ObjectMapper
 */
readonly class JsonObjectMapper implements ObjectMapper
{
    private ArrayObjectMapper $mapper;

    /**
     * @param TypeMapper $typeMappers
     */
    public function __construct(
        Reader $defaultTypeReader = new RuntimeReader(),
        Writer $defaultTypeWriter = new RuntimeWriter(),
        array $typeMappers = [],
    ) {
        $this->mapper = new ArrayObjectMapper($defaultTypeReader, $defaultTypeWriter, $typeMappers);
    }

    #[Override] public function readValueWith(string $type, mixed $value, array $with = [])
    {
        return $this->mapper->readValue($type, [
            ...json_decode($value, true, 512, JSON_THROW_ON_ERROR),
            ...$with,
        ]);
    }

    #[Override] public function readValue(string $type, mixed $value, array $trace = [])
    {
        return $this->readValueWith($type, $value);
    }

    public function writeValue(mixed $value): string
    {
        return json_encode($this->mapper->writeValue($value));
    }
}
