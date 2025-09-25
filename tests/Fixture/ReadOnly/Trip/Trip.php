<?php

namespace Tcds\Io\Serializer\Fixture\ReadOnly\Trip;

use Tcds\Io\Serializer\Fixture\ReadOnly\Place;
use Tcds\Io\Serializer\Fixture\ReadOnly\User;

readonly class Trip
{
    /**
     * @param Place[] $stops
     * @param TripStatus[] $status
     * @param string[] $remarks
     */
    public function __construct(
        public User $driver,
        public array $stops,
        public array $status,
        public array $remarks = [],
        public string $description = '',
    ) {
    }
}
