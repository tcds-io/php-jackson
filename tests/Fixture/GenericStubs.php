<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Jackson\Fixture;

use Tcds\Io\Generic\ArrayList;
use Tcds\Io\Generic\Map;
use Test\Tcds\Io\Jackson\Fixture\ReadOnly\Address;
use Test\Tcds\Io\Jackson\Fixture\ReadOnly\BankAccount;
use Test\Tcds\Io\Jackson\Fixture\ReadOnly\LatLng;
use Test\Tcds\Io\Jackson\Fixture\ReadOnly\User;
use Traversable;

readonly class GenericStubs
{
    /**
     * @param ArrayList<Address> $addresses
     * @param Traversable<User> $users
     * @param Map<string, LatLng> $positions
     * @param Pair<AccountType, BankAccount> $accounts
     */
    public function __construct(
        ArrayList $addresses,
        Traversable $users,
        Map $positions,
        Pair $accounts,
    ) {
    }
}
