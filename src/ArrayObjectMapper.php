<?php

namespace Tcds\Io\Jackson;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Override;
use Tcds\Io\Generic\Reflection\ReflectionFunction;
use Tcds\Io\Generic\Reflection\Type\Parser\TypeParser;
use Tcds\Io\Jackson\Exception\JacksonException;
use Tcds\Io\Jackson\Node\Mappers\Readers\DateTimeReader;
use Tcds\Io\Jackson\Node\Mappers\Writers\DateTimeWriter;
use Tcds\Io\Jackson\Node\Reader;
use Tcds\Io\Jackson\Node\Runtime\RuntimeReader;
use Tcds\Io\Jackson\Node\Runtime\RuntimeTypeNodeFactory;
use Tcds\Io\Jackson\Node\Runtime\RuntimeTypeNodeSpecificationFactory;
use Tcds\Io\Jackson\Node\Runtime\RuntimeWriter;
use Tcds\Io\Jackson\Node\StaticReader;
use Tcds\Io\Jackson\Node\StaticWriter;
use Tcds\Io\Jackson\Node\TypeNode;
use Tcds\Io\Jackson\Node\TypeNodeFactory;
use Tcds\Io\Jackson\Node\Writer;
use Throwable;

/**
 * @phpstan-import-type TypeMappers from ObjectMapper
 */
readonly class ArrayObjectMapper implements ObjectMapper
{
    private Reader $defaultTypeReader;
    private Writer $defaultTypeWriter;
    /** @var TypeMappers */
    private array $typeMappers;

    /**
     * @param Reader<mixed>|null $defaultTypeReader
     * @param Writer<mixed>|null $defaultTypeWriter
     * @param TypeMappers $typeMappers
     */
    public function __construct(
        ?Reader $defaultTypeReader = null,
        ?Writer $defaultTypeWriter = null,
        array $typeMappers = [],
        TypeNodeFactory $typeNodeFactory = new RuntimeTypeNodeFactory(),
    ) {
        $this->defaultTypeReader = $defaultTypeReader ?? new RuntimeReader(
            node: $typeNodeFactory,
            specification: new RuntimeTypeNodeSpecificationFactory(factory: $typeNodeFactory),
        );
        $this->defaultTypeWriter = $defaultTypeWriter ?? new RuntimeWriter(node: $typeNodeFactory);

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

    #[Override] public function readValue(string $type, mixed $value, array $path = []): mixed
    {
        [$main] = TypeParser::getGenericTypes($type);
        $reader = $this->typeMappers[$main]['reader'] ?? $this->defaultTypeReader;
        $callable = $reader instanceof StaticReader ? $reader::read(...) : $reader;

        try {
            return ReflectionFunction::call($callable(...), [
                'data' => $value,
                'type' => $type,
                'mapper' => $this,
                'path' => $path,
            ]);
        } catch (JacksonException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new JacksonException('Failed to read value', path: $path, previous: $e);
        }
    }

    #[Override]
    public function writeValue(mixed $value, ?string $type = null, array $path = []): mixed
    {
        $type ??= TypeNode::of($value);
        [$main] = TypeParser::getGenericTypes($type);
        $writer = $this->typeMappers[$main]['writer'] ?? $this->defaultTypeWriter;
        $callable = $writer instanceof StaticWriter ? $writer::write(...) : $writer;

        try {
            return ReflectionFunction::call($callable(...), [
                'data' => $value,
                'type' => $type,
                'mapper' => $this,
                'path' => $path,
            ]);
        } catch (JacksonException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new JacksonException('Failed to write value', path: $path, previous: $e);
        }
    }
}
