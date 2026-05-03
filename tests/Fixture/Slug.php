<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Jackson\Fixture;

use Tcds\Io\Jackson\Node\JsonMapper;

#[JsonMapper(reader: SlugReader::class, writer: SlugWriter::class)]
readonly class Slug
{
    public function __construct(public string $value)
    {
    }
}
