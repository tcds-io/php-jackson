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
use Tcds\Io\Serializer\Fixture\ReadOnly\Response;
use Tcds\Io\Serializer\Fixture\ReadOnly\User;
use Tcds\Io\Serializer\Fixture\WithShape;
use Tcds\Io\Serializer\Metadata\Node\ReadNode;
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
        $missingKeyGeneric = $this->expectThrows(SerializerException::class, fn() => TypeNode::from(Pair::class));
        $this->assertEquals(new SerializerException('No generic defined for template `K`'), $missingKeyGeneric);

        $missingKeyGeneric = $this->expectThrows(SerializerException::class, fn() => TypeNode::from(generic(Pair::class, ['string'])));
        $this->assertEquals(new SerializerException('No generic defined for template `V`'), $missingKeyGeneric);
    }

    #[Test] public function parse_type(): void
    {
        $type = Address::class;

        $node = TypeNode::from($type);
        $this->initializeNode($node);

        $this->assertEquals(Address::node(), $node);
    }

    #[Test] public function given_an_annotated_type_then_get_node(): void
    {
        $type = generic(ArrayList::class, [Address::class]);

        $node = TypeNode::from($type);
        $this->initializeNode($node);

        $this->assertEquals(
            new TypeNode(
                type: $type,
                inputs: [
                    'items' => new ReadNode(
                        name: 'items',
                        node: new TypeNode(
                            type: generic('list', [Address::class]),
                            inputs: [
                                'value' => new ReadNode('value', Address::node()),
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
        $this->initializeNode($node);

        $this->assertEquals(
            new TypeNode(
                type: GenericStubs::class,
                inputs: [
                    'addresses' => new ReadNode(
                        'addresses',
                        new TypeNode(
                            type: generic(ArrayList::class, [Address::class]),
                            inputs: [
                                'items' => new ReadNode(
                                    'items',
                                    node: new TypeNode(
                                        type: generic('list', [Address::class]),
                                        inputs: [
                                            'value' => new ReadNode('value', Address::node()),
                                        ],
                                    ),
                                ),
                            ],
                        ),
                    ),
                    'users' => new ReadNode(
                        'users',
                        new TypeNode(
                            type: generic(Traversable::class, [User::class]),
                            inputs: [
                                'value' => new ReadNode('value', User::node()),
                            ],
                        ),
                    ),
                    'positions' => new ReadNode(
                        name: 'positions',
                        node: new TypeNode(
                            type: generic(Map::class, ['string', LatLng::class]),
                            inputs: [
                                'entries' => new ReadNode(
                                    name: 'entries',
                                    node: new TypeNode(
                                        type: generic('map', ['string', LatLng::class]),
                                        inputs: [
                                            'key' => new ReadNode('key', new TypeNode('string')),
                                            'value' => new ReadNode('value', LatLng::node()),
                                        ],
                                    ),
                                ),
                            ],
                        ),
                    ),
                    'accounts' => new ReadNode(
                        name: 'accounts',
                        node: new TypeNode(
                            type: generic(Pair::class, [AccountType::class, BankAccount::class]),
                            inputs: [
                                'key' => new ReadNode('key', new TypeNode(type: AccountType::class)),
                                'value' => new ReadNode('value', BankAccount::node()),
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

        $node = TypeNode::from(generic($type, ['string']));
        $this->initializeNode($node);

        $this->assertEquals(
            new TypeNode(
                type: generic(ArrayList::class, ['string']),
                inputs: [
                    'items' => new ReadNode(
                        name: 'items',
                        node: new TypeNode(
                            type: 'list<string>',
                            inputs: [
                                'value' => new ReadNode('value', new TypeNode('string')),
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
        $node = TypeNode::from(generic($type, ['string', Address::class]));
        $this->initializeNode($node);

        $this->assertEquals(
            new TypeNode(
                type: generic(Pair::class, ['string', Address::class]),
                inputs: [
                    'key' => new ReadNode('key', new TypeNode(type: 'string')),
                    'value' => new ReadNode('value', Address::node()),
                ],
            ),
            $node,
        );
    }

    #[Test] public function parse_type_with_shapes(): void
    {
        $type = WithShape::class;

        $node = TypeNode::from($type);
        $this->initializeNode($node);

        $this->assertEquals(
            new TypeNode(
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
            ),
            $node,
        );
    }

    #[Test] public function array_map(): void
    {
        $type = Response::class;

        $node = TypeNode::from($type);
        $this->initializeNode($node);

        $this->assertEquals(
            new TypeNode(
                type: Response::class,
                inputs: [
                    '_meta' => new ReadNode(
                        name: '_meta',
                        node: new TypeNode(
                            type: 'map<string, mixed>',
                            inputs: [
                                'key' => new ReadNode('key', new TypeNode('string')),
                                'value' => new ReadNode('value', new TypeNode('mixed')),
                            ],
                        ),
                    ),
                ],
            ),
            $node,
        );
    }
}
