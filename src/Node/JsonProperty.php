<?php

declare(strict_types=1);

namespace Tcds\Io\Jackson\Node;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
final readonly class JsonProperty
{
    public function __construct(public string $name)
    {
    }
}
