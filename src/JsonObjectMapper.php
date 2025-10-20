<?php

namespace Tcds\Io\Serializer;

use Override;
use Tcds\Io\Serializer\Node\Json;
use Tcds\Io\Serializer\Node\Reader;
use Tcds\Io\Serializer\Node\Runtime\RuntimeReader;
use Tcds\Io\Serializer\Node\Runtime\RuntimeWriter;
use Tcds\Io\Serializer\Node\Writer;

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
            ...Json::decode($value),
            ...$with,
        ]);
    }

    #[Override] public function readValue(string $type, mixed $value, array $trace = []): mixed
    {
        return $this->readValueWith($type, $value);
    }

    public function writeValue(mixed $value, ?string $type = null): string
    {
        return Json::encode($this->mapper->writeValue($value, $type));
    }
}
