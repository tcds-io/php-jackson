<?php

declare(strict_types=1);

namespace Tcds\Io\Serializer\Fixture;

enum AccountType: string
{
    case CHECKING = 'checking';
    case SAVING = 'saving';
}
