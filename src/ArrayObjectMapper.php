<?php

namespace Tcds\Io\Serializer;

use Override;
use Tcds\Io\Serializer\Metadata\Parser\Type;
use Tcds\Io\Serializer\Metadata\Reader;
use Tcds\Io\Serializer\Metadata\Writer;
use Tcds\Io\Serializer\Runtime\RuntimeReader;
use Tcds\Io\Serializer\Runtime\RuntimeWriter;

/**
 * @phpstan-import-type TypeMapper from ObjectMapper
 */
readonly class ArrayObjectMapper implements ObjectMapper
{
    private array $typeMappers;

    /**
     * @param TypeMapper $typeMappers
     */
    public function __construct(
        private Reader $defaultTypeReader = new RuntimeReader(),
        private Writer $defaultTypeWriter = new RuntimeWriter(),
        array $typeMappers = [],
    ) {
        $this->typeMappers = [
            ...$typeMappers,
        ];
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

    #[Override] public function writeValue(mixed $value): mixed
    {
        $type = Type::ofValue($value);
        $writer = $this->typeMappers[$type]['writer'] ?? $this->defaultTypeWriter;

        return $writer($value);
    }
}
