<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Jackson\Fixture;

use Tcds\Io\Jackson\Node\JsonMapper;

#[JsonMapper(
    reader: new SlugReader(),
    writer: new SlugWriter()
)]
readonly class Slug
{
    public function __construct(public string $value)
    {
    }
}
