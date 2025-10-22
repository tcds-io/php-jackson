<?php

namespace Tcds\Io\Serializer;

use Tcds\Io\Serializer\Node\Reader;
use Tcds\Io\Serializer\Node\Writer;

/**
 * @phpstan-type TypeMappers array<string, array{ reader: Reader<mixed>, writer: Writer<mixed> }>
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
