<?php

declare(strict_types=1);

namespace Tcds\Io\Serializer\Node\Readers;

use DateTimeInterface;
use Override;
use Tcds\Io\Serializer\Node\Reader;
use Tcds\Io\Serializer\ObjectMapper;

/**
 * @implements Reader<DateTimeInterface>
 */
readonly class DateTimeReader implements Reader
{
    public function __construct(private ?string $type = null)
    {
    }

    /**
     * @template T of DateTimeInterface
     * @param class-string<T> $type
     * @param list<string> $trace
     */
    #[Override]
    public function __invoke(mixed $data, string $type, ObjectMapper $mapper, array $trace): ?DateTimeInterface
    {
        $type = $this->type ?? $type;

        /** @var DateTimeInterface|null */
        return !is_null($data) ? new $type($data) : null;
    }
}
