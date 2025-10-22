<?php

namespace Tcds\Io\Serializer;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Override;
use Tcds\Io\Serializer\Node\Reader;
use Tcds\Io\Serializer\Node\Readers\DateTimeReader;
use Tcds\Io\Serializer\Node\Runtime\RuntimeReader;
use Tcds\Io\Serializer\Node\Runtime\RuntimeWriter;
use Tcds\Io\Serializer\Node\TypeNode;
use Tcds\Io\Serializer\Node\Writer;
use Tcds\Io\Serializer\Node\Writers\DateTimeWriter;

/**
 * @phpstan-import-type TypeMappers from ObjectMapper
 */
readonly class ArrayObjectMapper implements ObjectMapper
{
    /** @var TypeMappers */
    private array $typeMappers;

    /**
     * @param Reader<mixed> $defaultTypeReader
     * @param Writer<mixed> $defaultTypeWriter
     * @param TypeMappers $typeMappers
     */
    public function __construct(
        private Reader $defaultTypeReader = new RuntimeReader(),
        private Writer $defaultTypeWriter = new RuntimeWriter(),
        array $typeMappers = [],
    ) {
        $dateTimeMapper = ['reader' => new DateTimeReader(), 'writer' => new DateTimeWriter()];

        $this->typeMappers = [
            DateTime::class => $dateTimeMapper,
            DateTimeImmutable::class => $dateTimeMapper,
            DateTimeInterface::class => [
                'reader' => new DateTimeReader(DateTime::class),
                'writer' => new DateTimeWriter(),
            ],
            'Carbon\Carbon' => $dateTimeMapper,
            'Carbon\CarbonImmutable' => $dateTimeMapper,
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
