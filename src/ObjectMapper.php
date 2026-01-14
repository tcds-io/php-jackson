<?php

namespace Tcds\Io\Jackson;

use Closure;
use Tcds\Io\Jackson\Exception\JacksonException;
use Tcds\Io\Jackson\Exception\UnableToParseValue;
use Tcds\Io\Jackson\Node\Reader;
use Tcds\Io\Jackson\Node\Writer;
use Tcds\Io\Jackson\Node\StaticReader;
use Tcds\Io\Jackson\Node\StaticWriter;

/**
 * @phpstan-type TypeMappers array<string, array{
 *     reader?: Reader<mixed>|StaticReader<mixed>|Closure(mixed $data, string $type, ObjectMapper $mapper, list<string> $path): mixed,
 *     writer?: Writer<mixed>|StaticWriter<mixed>|Closure(mixed $data, string $type, ObjectMapper $mapper, list<string> $path): mixed,
 * }>
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
     * @param list<string> $path
     * @return T
     * @throws UnableToParseValue
     * @throws JacksonException
     */
    public function readValue(string $type, mixed $value, array $path = []): mixed;

    /**
     * @param list<string> $path
     */
    public function writeValue(mixed $value, ?string $type = null, array $path = []): mixed;
}
