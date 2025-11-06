<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Jackson\Fixture;

enum AccountType: string
{
    case CHECKING = 'checking';
    case SAVING = 'saving';
}
