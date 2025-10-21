<?php

namespace Tcds\Io\Serializer;

use Tcds\Io\Serializer\Node\Reader;
use Tcds\Io\Serializer\Node\Writer;

/**
 * @phpstan-type Type string|class-string<mixed>
 * @phpstan-type ReaderFn callable(mixed $data, string $type, ArrayObjectMapper $mapper, list<string> $trace): mixed
 * @phpstan-type WriterFn callable(mixed $data, string $type, ObjectMapper $mapper): mixed
 * @phpstan-type TypeMapper array<Type, array{ reader: Reader|ReaderFn, writer: Writer|WriterFn }>
 */
interface ObjectMapper
{
    /**
     * @template T
     * @param class-string<T> $type
     * @param array<string, mixed> $with
     * @return T
     */
    public function readValueWith(string $type, mixed $value, array $with = []);

    /**
     * @template T
     * @param class-string<T> $type
     * @param list<string> $trace
     */
    public function readValue(string $type, mixed $value, array $trace = []): mixed;

    public function writeValue(mixed $value, ?string $type = null): mixed;
}
