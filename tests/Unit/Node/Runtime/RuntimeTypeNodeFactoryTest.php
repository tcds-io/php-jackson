<?php

declare(strict_types=1);

namespace Tcds\Io\Serializer\Unit\Node\Runtime;

use PHPUnit\Framework\Attributes\Test;
use Tcds\Io\Generic\ArrayList;
use Tcds\Io\Serializer\Fixture\AccountStatus;
use Tcds\Io\Serializer\Fixture\DataPayloadShape;
use Tcds\Io\Serializer\Fixture\Pair;
use Tcds\Io\Serializer\Fixture\ReadOnly\Address;
use Tcds\Io\Serializer\Fixture\ReadOnly\LatLng;
use Tcds\Io\Serializer\Fixture\ReadOnly\User;
use Tcds\Io\Serializer\Node\InputNode;
use Tcds\Io\Serializer\Node\Runtime\RuntimeTypeNodeFactory;
use Tcds\Io\Serializer\Node\TypeNode;
use Tcds\Io\Serializer\SerializerTestCase;

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
            ),
            $node,
        );
    }
}
