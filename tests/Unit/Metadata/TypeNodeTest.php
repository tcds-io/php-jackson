<?php

namespace Tcds\Io\Serializer\Unit\Metadata;

use PHPUnit\Framework\Attributes\Test;
use Tcds\Io\Generic\ArrayList;
use Tcds\Io\Generic\Map;
use Tcds\Io\Serializer\Exception\SerializerException;
use Tcds\Io\Serializer\Fixture\AccountType;
use Tcds\Io\Serializer\Fixture\GenericStubs;
use Tcds\Io\Serializer\Fixture\Pair;
use Tcds\Io\Serializer\Fixture\ReadOnly\Address;
use Tcds\Io\Serializer\Fixture\ReadOnly\BankAccount;
use Tcds\Io\Serializer\Fixture\ReadOnly\LatLng;
use Tcds\Io\Serializer\Fixture\ReadOnly\Place;
use Tcds\Io\Serializer\Fixture\ReadOnly\Response;
use Tcds\Io\Serializer\Fixture\ReadOnly\User;
use Tcds\Io\Serializer\Fixture\WithShape;
use Tcds\Io\Serializer\Metadata\ParamNode;
use Tcds\Io\Serializer\Metadata\TypeNode;
use Tcds\Io\Serializer\SerializerTestCase;
use Traversable;

class TypeNodeTest extends SerializerTestCase
{
    #[Test] public function scalar_nodes(): void
    {
        $this->assertEquals(new TypeNode('string'), TypeNode::from('string'));
        $this->assertEquals(new TypeNode('int'), TypeNode::from('int'));
        $this->assertEquals(new TypeNode('float'), TypeNode::from('float'));
        $this->assertEquals(new TypeNode('bool'), TypeNode::from('bool'));
    }

    #[Test] public function when_generics_are_missing_for_templates_then_throw_exception(): void
    {
        $missingKeyGeneric = $this->expectThrows(fn() => TypeNode::from(Pair::class));
        $this->assertEquals(new SerializerException('No generic defined for template `K`'), $missingKeyGeneric);

        $missingKeyGeneric = $this->expectThrows(fn() => TypeNode::from(Pair::class, ['string']));
        $this->assertEquals(new SerializerException('No generic defined for template `V`'), $missingKeyGeneric);
    }

    #[Test] public function parse_type(): void
    {
        $type = Address::class;

        $node = TypeNode::from($type);

        $this->assertEquals(
            new TypeNode(
                type: Address::class,
                params: [
                    'street' => new ParamNode(new TypeNode('string')),
                    'number' => new ParamNode(new TypeNode('int')),
                    'main' => new ParamNode(new TypeNode('bool')),
                    'place' => new ParamNode(Place::node()),
                ],
            ),
            $node,
        );
    }

    #[Test] public function given_an_annotated_type_then_get_node(): void
    {
        $type = generic(ArrayList::class, [Address::class]);

        $node = TypeNode::from($type);

        $this->assertEquals(
            new TypeNode(
                type: $type,
                params: [
                    'items' => new ParamNode(
                        type: new TypeNode(
                            type: generic('list', [Address::class]),
                            params: [
                                'value' => new ParamNode(type: Address::node()),
                            ],
                        ),
                    ),
                ],
            ),
            $node,
        );
    }

    #[Test] public function parse_with_lists(): void
    {
        $type = GenericStubs::class;

        $node = TypeNode::from($type);

        $this->assertEquals(
            new TypeNode(
                type: GenericStubs::class,
                params: [
                    'addresses' => new ParamNode(
                        new TypeNode(
                            type: generic(ArrayList::class, [Address::class]),
                            params: [
                                'items' => new ParamNode(
                                    type: new TypeNode(
                                        type: generic('list', [Address::class]),
                                        params: [
                                            'value' => new ParamNode(type: Address::node()),
                                        ],
                                    ),
                                ),
                            ],
                        ),
                    ),
                    'users' => new ParamNode(
                        new TypeNode(
                            type: generic(Traversable::class, [User::class]),
                            params: [
                                'value' => new ParamNode(type: User::node()),
                            ],
                        ),
                    ),
                    'positions' => new ParamNode(
                        type: new TypeNode(
                            type: generic(Map::class, ['string', LatLng::class]),
                            params: [
                                'entries' => new ParamNode(
                                    type: new TypeNode(
                                        type: generic('map', ['string', LatLng::class]),
                                        params: [
                                            'key' => new ParamNode(type: new TypeNode('string')),
                                            'value' => new ParamNode(type: LatLng::node()),
                                        ],
                                    ),
                                ),
                            ],
                        ),
                    ),
                    'accounts' => new ParamNode(
                        type: new TypeNode(
                            type: generic(Pair::class, [AccountType::class, BankAccount::class]),
                            params: [
                                'key' => new ParamNode(type: new TypeNode(type: AccountType::class)),
                                'value' => new ParamNode(type: BankAccount::node()),
                            ],
                        ),
                    ),
                ],
            ),
            $node,
        );
    }

    #[Test] public function parse_with_inner_generics(): void
    {
        $type = ArrayList::class;

        $node = TypeNode::from($type, ['string']);

        $this->assertEquals(
            new TypeNode(
                type: generic(ArrayList::class, ['string']),
                params: [
                    'items' => new ParamNode(
                        type: new TypeNode(
                            type: 'list<string>',
                            params: [
                                'value' => new ParamNode(new TypeNode('string')),
                            ],
                        ),
                    ),
                ],
            ),
            $node,
        );
    }

    #[Test] public function parse_type_with_generics(): void
    {
        $type = Pair::class;
        $generics = ['string', Address::class];

        $node = TypeNode::from($type, $generics);

        $this->assertEquals(
            new TypeNode(
                type: generic(Pair::class, ['string', Address::class]),
                params: [
                    'key' => new ParamNode(new TypeNode(type: 'string')),
                    'value' => new ParamNode(Address::node()),
                ],
            ),
            $node,
        );
    }

    #[Test] public function parse_type_with_shapes(): void
    {
        $type = WithShape::class;

        $node = TypeNode::from($type);

        $this->assertEquals(
            new TypeNode(
                type: WithShape::class,
                params: [
                    'data' => new ParamNode(
                        type: new TypeNode(
                            type: 'array',
                            params: [
                                'user' => new ParamNode(User::node()),
                                'address' => new ParamNode(Address::node()),
                                'description' => new ParamNode(new TypeNode('string')),
                            ],
                        ),
                    ),
                    'payload' => new ParamNode(
                        type: new TypeNode(
                            type: 'object',
                            params: [
                                'user' => new ParamNode(User::node()),
                                'address' => new ParamNode(Address::node()),
                                'description' => new ParamNode(new TypeNode('string')),
                            ],
                        ),
                    ),
                ],
            ),
            $node,
        );
    }

    #[Test] public function array_map(): void
    {
        $type = Response::class;

        $node = TypeNode::from($type);

        $this->assertEquals(
            new TypeNode(
                type: Response::class,
                params: [
                    '_meta' => new ParamNode(
                        type: new TypeNode(
                            type: 'map<string, mixed>',
                            params: [
                                'key' => new ParamNode(new TypeNode('string')),
                                'value' => new ParamNode(new TypeNode('mixed')),
                            ],
                        ),
                    ),
                ],
            ),
            $node,
        );
    }
}
