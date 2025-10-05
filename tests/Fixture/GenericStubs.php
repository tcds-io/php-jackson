<?php

declare(strict_types=1);

namespace Tcds\Io\Serializer\Fixture;

use Tcds\Io\Generic\ArrayList;
use Tcds\Io\Generic\Map;
use Tcds\Io\Serializer\Fixture\ReadOnly\Address;
use Tcds\Io\Serializer\Fixture\ReadOnly\BankAccount;
use Tcds\Io\Serializer\Fixture\ReadOnly\LatLng;
use Tcds\Io\Serializer\Fixture\ReadOnly\User;
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

    public static function fingerprint(): string
    {
        return sprintf(
            '%s[%s, %s, %s, %s]',
            self::class,
            sprintf('%s[%s[%s]]', ArrayList::class, 'list', Address::fingerprint()),
            sprintf('%s[%s]', Traversable::class, User::fingerprint()),
            sprintf('%s[%s[%s, %s]]', Map::class, 'array', 'string', LatLng::fingerprint()),
            sprintf('%s[%s, %s]', Pair::class, AccountType::class, BankAccount::fingerprint()),
        );
    }
}
