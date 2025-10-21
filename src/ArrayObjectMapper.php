<?php

namespace Tcds\Io\Serializer;

use Override;
use Tcds\Io\Serializer\Node\Reader;
use Tcds\Io\Serializer\Node\Runtime\RuntimeReader;
use Tcds\Io\Serializer\Node\Runtime\RuntimeWriter;
use Tcds\Io\Serializer\Node\TypeNode;
use Tcds\Io\Serializer\Node\Writer;

/**
 * @phpstan-import-type TypeMapper from ObjectMapper
 */
readonly class ArrayObjectMapper implements ObjectMapper
{
    /** @var TypeMapper */
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
        /** @var array<mixed> $value */
        return $this->readValue($type, [
            ...$value,
            ...$with,
        ]);
    }

    #[Override] public function readValue(string $type, mixed $value, array $trace = []): mixed
    {
        $reader = $this->typeMappers[$type]['reader'] ?? $this->defaultTypeReader;

        return $reader($value, $type, $this, $trace);
    }

    #[Override] public function writeValue(mixed $value, ?string $type = null): mixed
    {
        $type ??= TypeNode::of($value);
        $writer = $this->typeMappers[$type]['writer'] ?? $this->defaultTypeWriter;

        return $writer($value, $type, $this);
    }
}
