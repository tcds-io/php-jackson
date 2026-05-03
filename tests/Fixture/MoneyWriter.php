<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Jackson\Fixture;

use Override;
use Tcds\Io\Jackson\Node\StaticWriter;
use Tcds\Io\Jackson\ObjectMapper;

/**
 * @implements StaticWriter<Money>
 */
final class MoneyWriter implements StaticWriter
{
    /**
     * @param list<string> $path
     */
    #[Override] public static function write(mixed $data, string $type, ObjectMapper $mapper, array $path): ?string
    {
        if (!$data instanceof Money) {
            return null;
        }

        return sprintf('$%.2f', $data->cents / 100);
    }
}
