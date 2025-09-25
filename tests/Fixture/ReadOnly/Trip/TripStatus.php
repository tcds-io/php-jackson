<?php

namespace Tcds\Io\Serializer\Fixture\ReadOnly\Trip;

enum TripStatus: string
{
    case STARTED = 'Started';
    case FINALIZED = 'Finalized';
}
