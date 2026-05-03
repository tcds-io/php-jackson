<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Jackson\Fixture;

use Override;
use Tcds\Io\Jackson\Node\StaticReader;
use Tcds\Io\Jackson\ObjectMapper;

/**
 * @implements StaticReader<Money>
 */
final class MoneyReader implements StaticReader
{
    /**
     * @param list<string> $path
     */
    #[Override] public static function read(mixed $data, string $type, ObjectMapper $mapper, array $path): ?Money
    {
        if ($data === null) {
            return null;
        }

        if (is_int($data)) {
            return new Money($data);
        }

        if (is_string($data) && preg_match('/^\$(?<value>\d+(?:\.\d{1,2})?)$/', $data, $matches) === 1) {
            return new Money((int) round((float) $matches['value'] * 100));
        }

        throw new \InvalidArgumentException(sprintf('Cannot parse Money from %s', get_debug_type($data)));
    }
}
