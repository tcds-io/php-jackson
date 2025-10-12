<?php

namespace Tcds\Io\Serializer\Unit\Metadata\Parser;

use PHPUnit\Framework\Attributes\Test;
use Tcds\Io\Generic\ArrayList;
use Tcds\Io\Generic\Map;
use Tcds\Io\Serializer\Fixture\AccountType;
use Tcds\Io\Serializer\Fixture\GenericStubs;
use Tcds\Io\Serializer\Fixture\Pair;
use Tcds\Io\Serializer\Fixture\ReadOnly\Address;
use Tcds\Io\Serializer\Fixture\ReadOnly\BankAccount;
use Tcds\Io\Serializer\Fixture\ReadOnly\LatLng;
use Tcds\Io\Serializer\Fixture\ReadOnly\Place;
use Tcds\Io\Serializer\Fixture\ReadOnly\User;
use Tcds\Io\Serializer\Fixture\WithShape;
use Tcds\Io\Serializer\Metadata\Node\ReadNode;
use Tcds\Io\Serializer\Metadata\Parser\ClassParams;
use Tcds\Io\Serializer\Metadata\TypeNode;
use Tcds\Io\Serializer\SerializerTestCase;
use Traversable;

class ClassParamsTest extends SerializerTestCase
{
    #[Test] public function params_of_pair(): void
    {
        $type = generic(Pair::class, ['string', 'float']);

        $params = ClassParams::of($type);

        $this->assertEquals(
            expected: [
                'key' => new ReadNode('key', new TypeNode('string')),
                'value' => new ReadNode('value', new TypeNode('float')),
            ],
            actual: $params,
        );
    }

    #[Test] public function params_of_array_list(): void
    {
        $class = ArrayList::class;

        $params = ClassParams::of(class: $class);

        $this->assertEquals(
            expected: [
                'items' => 'list<GenericItem>',
            ],
            actual: $params,
        );
    }

    #[Test] public function params_of_address(): void
    {
        $class = Address::class;

        $params = ClassParams::of(class: $class);

        $this->assertEquals(
            expected: [
                'street' => 'string',
                'number' => 'int',
                'main' => 'bool',
                'place' => Place::class,
            ],
            actual: $params,
        );
    }

    #[Test] public function params_of_generic_stubs(): void
    {
        $class = GenericStubs::class;

        $params = ClassParams::of(class: $class);

        $this->assertEquals(
            expected: [
                'addresses' => sprintf('%s<%s>', ArrayList::class, Address::class),
                'users' => sprintf('%s<%s>', Traversable::class, User::class),
                'positions' => sprintf('%s<%s, %s>', Map::class, 'string', LatLng::class),
                'accounts' => sprintf('%s<%s, %s>', Pair::class, AccountType::class, BankAccount::class),
            ],
            actual: $params,
        );
    }

    #[Test] public function params_of_class_with_shaped(): void
    {
        $type = WithShape::class;

        $params = ClassParams::of(class: $type);

        $this->assertEquals(
            expected: [
                'data' => sprintf(
                    'array{ user: %s, address: %s, description: %s }',
                    User::class,
                    Address::class,
                    'string',
                ),
                'payload' => sprintf(
                    'object{ user: %s, address: %s, description: %s }',
                    User::class,
                    Address::class,
                    'string',
                ),
            ],
            actual: $params,
        );
    }
}
