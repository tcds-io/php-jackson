<?php

namespace Test\Tcds\Io\Jackson\Fixture;

enum AccountStatus: string
{
    case ACTIVE = 'Active';
    case FINALISED = 'Finalized';
}
