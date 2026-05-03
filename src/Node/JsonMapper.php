<?php

declare(strict_types=1);

namespace Tcds\Io\Jackson\Node;

use Attribute;

/**
 * Class-level attribute that pins a custom reader and/or writer to a class.
 *
 * Both arguments take a class string. The class must implement either the
 * instance interface (`Reader`/`Writer`) or the static one
 * (`StaticReader`/`StaticWriter`); the instance is built with a no-arg
 * constructor when needed. PHP attributes cannot carry closures, so for
 * Closure-shaped mappers use the `typeMappers` array on `ArrayObjectMapper`
 * instead.
 *
 * Resolution order on every call:
 *   1. `typeMappers` constructor argument (most specific, wins)
 *   2. `#[JsonMapper]` attribute on the target class
 *   3. default reader/writer
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class JsonMapper
{
    /**
     * @param class-string<Reader<mixed>|StaticReader<mixed>>|null $reader
     * @param class-string<Writer<mixed>|StaticWriter<mixed>>|null $writer
     */
    public function __construct(
        public ?string $reader = null,
        public ?string $writer = null,
    ) {
    }
}
