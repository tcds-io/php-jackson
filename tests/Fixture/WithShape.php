<?php

namespace Tcds\Io\Serializer\Fixture;

use Tcds\Io\Serializer\Fixture\ReadOnly\Address;
use Tcds\Io\Serializer\Fixture\ReadOnly\User;

readonly class WithShape
{
    /**
     * @param array{
     *     user: User,
     *     address: Address,
     *     description: string,
     * } $data
     * @param object{
     *     user: User,
     *     address: Address,
     *     description: string,
     * } $payload
     */
    public function __construct(public array $data, public object $payload)
    {
    }

    public static function fingerprint(): string
    {
        return sprintf(
            '%s[%s, %s]',
            WithShape::class,
            sprintf('%s[%s, %s, %s]', 'array', User::fingerprint(), Address::fingerprint(), 'string'),
            sprintf('%s[%s, %s, %s]', 'object', User::fingerprint(), Address::fingerprint(), 'string'),
        );
    }
}
