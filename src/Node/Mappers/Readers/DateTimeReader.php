<?php

declare(strict_types=1);

namespace Tcds\Io\Jackson\Node\Mappers\Readers;

use DateTimeInterface;
use Override;
use Tcds\Io\Jackson\Node\Reader;
use Tcds\Io\Jackson\ObjectMapper;

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
     * @param list<string> $path
     */
    #[Override]
    public function __invoke(mixed $data, string $type, ObjectMapper $mapper, array $path): ?DateTimeInterface
    {
        $type = $this->type ?? $type;

        /** @var DateTimeInterface|null */
        return !is_null($data) ? new $type($data) : null;
    }
}
