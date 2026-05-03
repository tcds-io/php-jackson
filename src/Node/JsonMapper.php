<?php

declare(strict_types=1);

namespace Tcds\Io\Jackson\Node;

use Attribute;
use Closure;
use Tcds\Io\Jackson\ObjectMapper;

/**
 * Class-level attribute that pins a custom reader and/or writer to a class.
 *
 * Both arguments take a class string. Accepted shapes:
 *   - implementations of `Reader`/`Writer` (instance interface)
 *   - implementations of `StaticReader`/`StaticWriter` (no instantiation)
 *   - any class with an `__invoke` matching `MapperClosure` from
 *     {@see ObjectMapper} (a reader/writer "in closure form")
 *
 * The class is instantiated with a no-arg constructor when an instance is
 * needed. PHP attributes cannot carry literal closures — use the
 * `typeMappers` array on `ArrayObjectMapper` for those.
 *
 * Resolution order on every call:
 *   1. `typeMappers` constructor argument (most specific, wins)
 *   2. `#[JsonMapper]` attribute on the target class
 *   3. default reader/writer
 *
 * @phpstan-import-type MapperClosure from ObjectMapper
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class JsonMapper
{
    /**
     * @param class-string<Reader<mixed>|StaticReader<mixed>>|class-string|MapperClosure|null $reader
     * @param class-string<Writer<mixed>|StaticWriter<mixed>>|class-string|MapperClosure|null $writer
     */
    public function __construct(
        public Closure|string|null $reader = null,
        public Closure|string|null $writer = null,
    ) {
    }
}
