<?php

namespace Tcds\Io\Serializer;

use Override;
use Tcds\Io\Serializer\Mapper\Reader;
use Tcds\Io\Serializer\Runtime\RuntimeReader;

/**
 * @phpstan-import-type TypeMapper from ObjectMapper
 */
readonly class JsonObjectMapper implements ObjectMapper
{
    private ArrayObjectMapper $mapper;

    /**
     * @param TypeMapper $typeMappers
     */
    public function __construct(Reader $defaultTypeReader = new RuntimeReader(), array $typeMappers = [])
    {
        $this->mapper = new ArrayObjectMapper($defaultTypeReader, $typeMappers);
    }

    #[Override] public function readValueWith(string $type, mixed $value, array $with = [])
    {
        return $this->mapper->readValue($type, [
            ...json_decode($value, true),
            ...$with,
        ]);
    }

    #[Override] public function readValue(string $type, mixed $value, array $trace = [])
    {
        return $this->readValueWith($type, $value);
    }
}
