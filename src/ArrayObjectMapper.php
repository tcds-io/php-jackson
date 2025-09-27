<?php

namespace Tcds\Io\Serializer;

use Override;
use Tcds\Io\Serializer\Mapper\Reader;
use Tcds\Io\Serializer\Runtime\RuntimeReader;

/**
 * @phpstan-import-type TypeMapper from ObjectMapper
 */
readonly class ArrayObjectMapper implements ObjectMapper
{
    /**
     * @param TypeMapper $typeMappers
     */
    public function __construct(
        private Reader $defaultTypeReader = new RuntimeReader(),
        private array $typeMappers = [],
    ) {
    }

    #[Override] public function readValueWith(string $type, mixed $value, array $with = [])
    {
        return $this->readValue($type, [...$value, ...$with]);
    }

    #[Override] public function readValue(string $type, mixed $value, array $trace = [])
    {
        $reader = $this->typeMappers[$type]['reader'] ?? $this->defaultTypeReader;

        return $reader($value, $this, $type, $trace);
    }
}
