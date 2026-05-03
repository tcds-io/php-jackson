<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Jackson\Fixture\ReadOnly;

use Tcds\Io\Jackson\Node\JsonProperty;

readonly class SnakeCaseDto
{
    public function __construct(
        #[JsonProperty('first_name')] public string $firstName,
        #[JsonProperty('last_name')] public string $lastName,
        public int $age,
    ) {
    }
}
