<?php

namespace Tcds\Io\Serializer\Reflection;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tcds\Io\Generic\ArrayList;
use Tcds\Io\Serializer\Fixture\Pair;
use Tcds\Io\Serializer\Fixture\ReadOnly\Address;
use Tcds\Io\Serializer\Fixture\ReadOnly\LatLng;
use Tcds\Io\Serializer\Fixture\ReadOnly\Place;
use Tcds\Io\Serializer\Fixture\ReadOnly\User;
use Tcds\Io\Serializer\Fixture\WithShape;
use Tcds\Io\Serializer\Metadata\Node\ReadNode;

class SerializerReflectionClassTest extends TestCase
{
    #[Test] public function get_pair_inputs(): void
    {
        $type = generic(Pair::class, ['string', 'string']);
        $reflection = new SerializerReflection($type);

        $inputs = $reflection->getInputs();

        $this->assertEquals(
            [
                new ReadNode('key', 'string'),
                new ReadNode('value', 'string'),
            ],
            $inputs,
        );
    }

    #[Test] public function get_address_inputs(): void
    {
        $reflection = new SerializerReflection(Address::class);

        $inputs = $reflection->getInputs();

        $this->assertEquals(
            [
                new ReadNode('street', 'string'),
                new ReadNode('number', 'int'),
                new ReadNode('main', 'bool'),
                new ReadNode('place', Place::class),
            ],
            $inputs,
        );
    }

    #[Test] public function get_array_list_inputs(): void
    {
        $type = generic(ArrayList::class, [LatLng::class]);
        $reflection = new SerializerReflection($type);

        $inputs = $reflection->getInputs();

        $this->assertEquals(
            [
                new ReadNode('items', generic('list', [LatLng::class])),
            ],
            $inputs,
        );
    }

    #[Test] public function get_with_shape_inputs(): void
    {
        $reflection = new SerializerReflection(WithShape::class);
        $shapedType = json_encode(['user' => User::class, 'address' => Address::class, 'description' => 'string']);

        $inputs = $reflection->getInputs();

        $this->assertEquals(
            [
                new ReadNode('data', "array$shapedType"),
                new ReadNode('payload', "object$shapedType"),
            ],
            $inputs,
        );
    }
}
