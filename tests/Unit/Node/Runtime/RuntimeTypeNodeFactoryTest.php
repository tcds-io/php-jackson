<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Jackson\Unit\Node\Runtime;

use PHPUnit\Framework\Attributes\Test;
use Tcds\Io\Generic\ArrayList;
use Tcds\Io\Jackson\Node\InputNode;
use Tcds\Io\Jackson\Node\OutputNode;
use Tcds\Io\Jackson\Node\Runtime\RuntimeTypeNodeFactory;
use Tcds\Io\Jackson\Node\TypeNode;
use Test\Tcds\Io\Jackson\Fixture\AccountStatus;
use Test\Tcds\Io\Jackson\Fixture\Credentials;
use Test\Tcds\Io\Jackson\Fixture\DataPayloadShape;
use Test\Tcds\Io\Jackson\Fixture\Pair;
use Test\Tcds\Io\Jackson\Fixture\ReadOnly\Address;
use Test\Tcds\Io\Jackson\Fixture\ReadOnly\LatLng;
use Test\Tcds\Io\Jackson\Fixture\ReadOnly\User;
use Test\Tcds\Io\Jackson\SerializerTestCase;

class RuntimeTypeNodeFactoryTest extends SerializerTestCase
{
    private RuntimeTypeNodeFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new RuntimeTypeNodeFactory();
    }

    #[Test]
    public function primitive(): void
    {
        $this->assertEquals(new TypeNode(type: 'int'), $this->factory->create('int'));
        $this->assertEquals(new TypeNode(type: 'string'), $this->factory->create('string'));
        $this->assertEquals(new TypeNode(type: 'int|float'), $this->factory->create('int|float'));
    }

    #[Test]
    public function enum(): void
    {
        $type = AccountStatus::class;

        $node = $this->factory->create($type);

        $this->assertEquals(new TypeNode(type: AccountStatus::class), $node);
    }

    #[Test]
    public function generic_list(): void
    {
        $type = generic('list', [LatLng::class]);

        $node = $this->factory->create($type);

        $this->assertEquals(
            new TypeNode(
                type: $type,
                inputs: [
                    new InputNode(name: 'value', type: LatLng::class),
                ],
                outputs: [],
            ),
            $node,
        );
    }

    #[Test]
    public function array_shape(): void
    {
        $type = shape('array', ['user' => User::class, 'address' => Address::class, 'description' => 'string']);

        $node = $this->factory->create($type);

        $this->assertEquals(
            new TypeNode(
                type: $type,
                inputs: [
                    new InputNode(name: 'user', type: User::class),
                    new InputNode(name: 'address', type: Address::class),
                    new InputNode(name: 'description', type: 'string'),
                ],
                outputs: [
                    OutputNode::param(name: 'user', type: User::class),
                    OutputNode::param(name: 'address', type: Address::class),
                    OutputNode::param(name: 'description', type: 'string'),
                ],
            ),
            $node,
        );
    }

    #[Test]
    public function object_shape(): void
    {
        $type = shape('object', ['user' => User::class, 'address' => Address::class, 'description' => 'string']);

        $node = $this->factory->create($type);

        $this->assertEquals(
            new TypeNode(
                type: $type,
                inputs: [
                    new InputNode(name: 'user', type: User::class),
                    new InputNode(name: 'address', type: Address::class),
                    new InputNode(name: 'description', type: 'string'),
                ],
                outputs: [
                    OutputNode::property(name: 'user', type: User::class),
                    OutputNode::property(name: 'address', type: Address::class),
                    OutputNode::property(name: 'description', type: 'string'),
                ],
            ),
            $node,
        );
    }

    #[Test]
    public function array_map(): void
    {
        $type = generic('array', ['string', Address::class]);

        $node = $this->factory->create($type);

        $this->assertEquals(
            new TypeNode(
                type: generic('map', ['string', Address::class]),
                inputs: [
                    new InputNode(name: 'key', type: 'string'),
                    new InputNode(name: 'value', type: Address::class),
                ],
                outputs: [],
            ),
            $node,
        );
    }

    #[Test]
    public function class_normal(): void
    {
        $type = Address::class;

        $node = $this->factory->create($type);

        $this->assertEquals(Address::node(), $node);
    }

    #[Test]
    public function class_with_generics(): void
    {
        $type = generic(Pair::class, ['string', LatLng::class]);

        $node = $this->factory->create($type);

        $this->assertEquals(Pair::node(), $node);
    }

    #[Test]
    public function class_with_generics_and_generic_param(): void
    {
        $type = generic(ArrayList::class, [LatLng::class]);

        $node = $this->factory->create($type);

        $this->assertEquals(
            new TypeNode(
                type: $type,
                inputs: [
                    new InputNode(name: 'items', type: generic('list', [LatLng::class])),
                ],
                outputs: [
                    OutputNode::method(name: 'items', accessor: 'items', type: generic('list', [LatLng::class])),
                ],
            ),
            $node,
        );
    }

    #[Test]
    public function class_with_shaped_params(): void
    {
        $type = DataPayloadShape::class;
        $params = ['user' => User::class, 'address' => Address::class, 'description' => 'string'];

        $node = $this->factory->create($type);

        $this->assertEquals(
            new TypeNode(
                type: $type,
                inputs: [
                    new InputNode(name: 'data', type: shape('array', $params)),
                    new InputNode(name: 'payload', type: shape('object', $params)),
                ],
                outputs: [
                    OutputNode::property(name: 'data', type: shape('array', $params)),
                    OutputNode::property(name: 'payload', type: shape('object', $params)),
                ],
            ),
            $node,
        );
    }

    #[Test]
    public function private_properties(): void
    {
        $type = Credentials::class;

        $node = $this->factory->create($type);

        $this->assertEquals(
            new TypeNode(
                type: $type,
                inputs: [
                    new InputNode(name: 'user', type: User::class),
                    new InputNode(name: 'login', type: 'string'),
                    new InputNode(name: 'password', type: 'string'),
                    new InputNode(name: 'valid', type: 'bool'),
                    new InputNode(name: 'expired', type: 'bool'),
                ],
                outputs: [
                    OutputNode::property(name: 'user', type: User::class),
                    OutputNode::method(name: 'login', accessor: 'login', type: 'string'),
                    OutputNode::method(name: 'password', accessor: 'getPassword', type: 'string'),
                    OutputNode::method(name: 'valid', accessor: 'isValid', type: 'bool'),
                    OutputNode::method(name: 'expired', accessor: 'hasExpired', type: 'bool'),
                ],
            ),
            $node,
        );
    }
}
