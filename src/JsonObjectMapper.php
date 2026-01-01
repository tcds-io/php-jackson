<?php

namespace Tcds\Io\Jackson;

use Override;
use Tcds\Io\Jackson\Node\Json;
use Tcds\Io\Jackson\Node\Reader;
use Tcds\Io\Jackson\Node\Runtime\RuntimeReader;
use Tcds\Io\Jackson\Node\Runtime\RuntimeWriter;
use Tcds\Io\Jackson\Node\Writer;

/**
 * @phpstan-import-type TypeMappers from ObjectMapper
 */
readonly class JsonObjectMapper implements ObjectMapper
{
    private ArrayObjectMapper $mapper;

    /**
     * @param Reader<mixed> $defaultTypeReader
     * @param Writer<mixed> $defaultTypeWriter
     * @param TypeMappers $typeMappers
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

    #[Override] public function readValue(string $type, mixed $value, array $path = []): mixed
    {
        return $this->readValueWith($type, $value);
    }

    #[Override]
    public function writeValue(mixed $value, ?string $type = null, array $path = []): string
    {
        return Json::encode($this->mapper->writeValue($value, $type, $path));
    }
}
