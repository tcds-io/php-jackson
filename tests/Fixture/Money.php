<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Jackson\Fixture;

use Tcds\Io\Jackson\Node\JsonMapper;

#[
    JsonMapper(
        reader: MoneyReader::class,
        writer: MoneyWriter::class,
    )
]
readonly class Money
{
    public function __construct(public int $cents)
    {
    }
}
