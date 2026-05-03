<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Jackson\Fixture;

use Tcds\Io\Jackson\Node\Writer;
use Tcds\Io\Jackson\ObjectMapper;

/**
 * Plain invokable — does NOT implement Writer/StaticWriter.
 */
final class SlugWriter implements Writer
{
    /**
     * @param list<string> $path
     */
    public function __invoke(mixed $data, string $type, ObjectMapper $mapper, array $path): ?string
    {
        return $data instanceof Slug ? $data->value : null;
    }
}
