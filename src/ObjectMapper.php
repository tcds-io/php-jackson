<?php

namespace Tcds\Io\Jackson;

use Tcds\Io\Jackson\Exception\JacksonException;
use Tcds\Io\Jackson\Exception\UnableToParseValue;
use Tcds\Io\Jackson\Node\Reader;
use Tcds\Io\Jackson\Node\Writer;

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
     * @param list<string> $path
     * @throws UnableToParseValue
     * @throws JacksonException
     */
    public function readValue(string $type, mixed $value, array $path = []): mixed;

    /**
     * @param list<string> $path
     */
    public function writeValue(mixed $value, ?string $type = null, array $path = []): mixed;
}
