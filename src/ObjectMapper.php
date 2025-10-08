<?php

namespace Tcds\Io\Serializer;

use Tcds\Io\Serializer\Metadata\Reader;
use Tcds\Io\Serializer\Metadata\Writer;

/**
 * @phpstan-type Type string|class-string<mixed>
 * @phpstan-type ReaderFn Reader::__invoke
 * @phpstan-type WriterFn Writer::__invoke
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
     * @return T
     */
    public function readValue(string $type, mixed $value, array $trace = []);

    public function writeValue(mixed $value): mixed;
}
