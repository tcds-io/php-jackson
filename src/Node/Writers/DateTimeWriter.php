<?php

declare(strict_types=1);

namespace Tcds\Io\Jackson\Node\Writers;

use DateTimeInterface;
use Override;
use Tcds\Io\Jackson\Node\Writer;
use Tcds\Io\Jackson\ObjectMapper;

/**
 * @implements Writer<DateTimeInterface>
 */
readonly class DateTimeWriter implements Writer
{
    #[Override] public function __invoke(mixed $data, string $type, ObjectMapper $mapper): ?string
    {
        return $data?->format(DateTimeInterface::ATOM);
    }
}
