<?php

namespace Test\Tcds\Io\Jackson\Fixture;

use Test\Tcds\Io\Jackson\Fixture\ReadOnly\Address;
use Test\Tcds\Io\Jackson\Fixture\ReadOnly\User;

readonly class DataPayloadShape
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
    public function __construct(public array $data, public object $payload) {}

    /**
     * @return array{
     *      user: User,
     *      address: Address,
     *      description: string,
     *  }
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return object{
     *      user: User,
     *      address: Address,
     *      description: string,
     *  }
     */
    public function getPayload(): object
    {
        return $this->payload;
    }
}
