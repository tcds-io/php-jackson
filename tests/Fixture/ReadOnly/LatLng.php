<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Jackson\Fixture\ReadOnly;

readonly class LatLng
{
    public function __construct(
        public float $lat,
        public float $lng,
    ) {
    }
}
