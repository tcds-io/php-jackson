<?php

declare(strict_types=1);

namespace Tcds\Io\Serializer\Node\Writers;

use DateTimeInterface;
use Override;
use Tcds\Io\Serializer\Node\Writer;
use Tcds\Io\Serializer\ObjectMapper;

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
