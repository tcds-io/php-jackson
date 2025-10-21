<?php

declare(strict_types=1);

namespace Tcds\Io\Serializer\Fixture\ReadOnly;

readonly class Place
{
    public function __construct(
        public string $city,
        public string $country,
        public LatLng $position,
    ) {
    }
}
