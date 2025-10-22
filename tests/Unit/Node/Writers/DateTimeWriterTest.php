<?php

declare(strict_types=1);

namespace Tcds\Io\Serializer\Unit\Node\Writers;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateTime;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use Tcds\Io\Serializer\Fixture\WithDateTime;
use Tcds\Io\Serializer\SerializerTestCase;

class DateTimeWriterTest extends SerializerTestCase
{
    #[Test]
    public function parse_datetime_values(): void
    {
        $data = new WithDateTime(
            datetime: new DateTime('2025-10-22T11:21:31'),
            datetimeImmutable: new DateTimeImmutable('2025-10-22T12:22:32'),
            carbon: new Carbon('2025-10-22T13:23:33'),
            carbonImmutable: new CarbonImmutable('2025-10-22T14:24:34'),
            datetimeInterface: new DateTime('2025-10-22T10:20:30'),
        );

        $data = $this->arrayMapper->writeValue($data);

        $this->assertEquals(
            [
                'datetime' => '2025-10-22T11:21:31+00:00',
                'datetimeImmutable' => '2025-10-22T12:22:32+00:00',
                'carbon' => '2025-10-22T13:23:33+00:00',
                'carbonImmutable' => '2025-10-22T14:24:34+00:00',
                'datetimeInterface' => '2025-10-22T10:20:30+00:00',
            ],
            $data,
        );
    }
}
