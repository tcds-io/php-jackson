<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Jackson\Fixture;

use Tcds\Io\Jackson\ObjectMapper;

/**
 * Plain invokable — does NOT implement Reader/StaticReader.
 * The class-string still resolves through #[JsonMapper] because __invoke
 * matches MapperClosure shape.
 */
final class SlugReader
{
    /**
     * @param list<string> $path
     */
    public function __invoke(mixed $data, string $type, ObjectMapper $mapper, array $path): ?Slug
    {
        if ($data === null) {
            return null;
        }

        $slug = strtolower((string) $data);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
        $slug = trim($slug, '-');

        return new Slug($slug);
    }
}
