<?php

namespace Tcds\Io\Serializer\Fixture;

use Tcds\Io\Serializer\Fixture\ReadOnly\Address;
use Tcds\Io\Serializer\Fixture\ReadOnly\User;
use Tcds\Io\Serializer\Metadata\Node\ReadNode;
use Tcds\Io\Serializer\Metadata\TypeNode;

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

    public static function node(): TypeNode
    {
        return new TypeNode(
            type: WithShape::class,
            inputs: [
                'data' => new ReadNode(
                    name: 'data',
                    node: new TypeNode(
                        type: shape('array', ['user' => User::class, 'address' => Address::class, 'description' => 'string']),
                        inputs: [
                            'user' => new ReadNode('user', User::node()),
                            'address' => new ReadNode('address', Address::node()),
                            'description' => new ReadNode('description', new TypeNode('string')),
                        ],
                    ),
                ),
                'payload' => new ReadNode(
                    name: 'payload',
                    node: new TypeNode(
                        type: shape('object', ['user' => User::class, 'address' => Address::class, 'description' => 'string']),
                        inputs: [
                            'user' => new ReadNode('user', User::node()),
                            'address' => new ReadNode('address', Address::node()),
                            'description' => new ReadNode('description', new TypeNode('string')),
                        ],
                    ),
                ),
            ],
        );
    }
}
