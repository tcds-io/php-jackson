<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Jackson\Fixture\ReadOnly;

use Tcds\Io\Jackson\Node\JsonProperty;

readonly class SnakeCaseWrapper
{
    public function __construct(
        #[JsonProperty('snake_field')] public SnakeCaseDto $snakeField,
        public string $label,
    ) {
    }
}
