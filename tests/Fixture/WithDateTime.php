<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Jackson\Fixture;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;

readonly class WithDateTime
{
    public function __construct(
        public DateTime $datetime,
        public DateTimeImmutable $datetimeImmutable,
        public Carbon $carbon,
        public CarbonImmutable $carbonImmutable,
        public DateTimeInterface $datetimeInterface,
    ) {
    }
}
