<?php

namespace Tcds\Io\Serializer\Fixture;

enum AccountStatus: string
{
    case ACTIVE = 'Active';
    case FINALISED = 'Finalized';
}
