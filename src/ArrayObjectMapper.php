<?php

namespace Tcds\Io\Jackson;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Closure;
use Override;
use ReflectionClass;
use Tcds\Io\Generic\Reflection\ReflectionFunction;
use Tcds\Io\Generic\Reflection\Type\Parser\DocBlockTypeResolver;
use Tcds\Io\Jackson\Exception\JacksonException;
use Tcds\Io\Jackson\Node\JsonMapper;
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
    /** @var Reader<mixed> */
    private Reader $defaultTypeReader;
    /** @var Writer<mixed> */
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
        [$main] = DocBlockTypeResolver::instance()->genericTypeParts($type);
        $callable = $this->resolveReader($main);

        try {
            return ReflectionFunction::call($callable, [
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
        [$main] = DocBlockTypeResolver::instance()->genericTypeParts($type);
        $callable = $this->resolveWriter($main);

        try {
            return ReflectionFunction::call($callable, [
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

    private function resolveReader(string $main): Closure
    {
        if (isset($this->typeMappers[$main]['reader'])) {
            $reader = $this->typeMappers[$main]['reader'];

            if ($reader instanceof Closure) {
                return $reader;
            }

            return $reader instanceof StaticReader ? $reader::read(...) : $reader->__invoke(...);
        }

        $value = $this->classAttribute($main)?->reader;

        if ($value instanceof Closure) {
            return $value;
        }

        if ($value !== null) {
            if (is_subclass_of($value, StaticReader::class)) {
                return $value::read(...);
            }

            $instance = new $value();

            if (!is_callable($instance)) {
                throw new JacksonException(sprintf('%s must implement Reader, StaticReader, or be invokable', $value));
            }

            return Closure::fromCallable($instance);
        }

        $reader = $this->defaultTypeReader;

        return $reader->__invoke(...);
    }

    private function resolveWriter(string $main): Closure
    {
        if (isset($this->typeMappers[$main]['writer'])) {
            $writer = $this->typeMappers[$main]['writer'];

            if ($writer instanceof Closure) {
                return $writer;
            }

            return $writer instanceof StaticWriter ? $writer::write(...) : $writer->__invoke(...);
        }

        $value = $this->classAttribute($main)?->writer;

        if ($value instanceof Closure) {
            return $value;
        }

        if ($value !== null) {
            if (is_subclass_of($value, StaticWriter::class)) {
                return $value::write(...);
            }

            $instance = new $value();

            if (!is_callable($instance)) {
                throw new JacksonException(sprintf('%s must implement Writer, StaticWriter, or be invokable', $value));
            }

            return Closure::fromCallable($instance);
        }

        $writer = $this->defaultTypeWriter;

        return $writer->__invoke(...);
    }

    private function classAttribute(string $type): ?JsonMapper
    {
        if (!class_exists($type)) {
            return null;
        }

        $attributes = new ReflectionClass($type)->getAttributes(JsonMapper::class);

        return $attributes === [] ? null : $attributes[0]->newInstance();
    }
}
