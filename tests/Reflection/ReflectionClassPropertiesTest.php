<?php

namespace Tcds\Io\Serializer\Reflection;

use PHPUnit\Framework\Attributes\Test;
use Tcds\Io\Generic\ArrayList;
use Tcds\Io\Serializer\Fixture\Pair;
use Tcds\Io\Serializer\Fixture\ReadOnly\Address;
use Tcds\Io\Serializer\Fixture\ReadOnly\Company;
use Tcds\Io\Serializer\Fixture\ReadOnly\LatLng;
use Tcds\Io\Serializer\Fixture\ReadOnly\Place;
use Tcds\Io\Serializer\Fixture\ReadOnly\User;
use Tcds\Io\Serializer\Fixture\WithShape;
use Tcds\Io\Serializer\Reflection\Type\ClassReflectionType;
use Tcds\Io\Serializer\Reflection\Type\GenericReflectionType;
use Tcds\Io\Serializer\Reflection\Type\PrimitiveReflectionType;
use Tcds\Io\Serializer\Reflection\Type\ShapeReflectionType;
use Tcds\Io\Serializer\SerializerTestCase;

class ReflectionClassPropertiesTest extends SerializerTestCase
{
    #[Test] public function get_pair_inputs(): void
    {
        $type = generic(Pair::class, ['string', 'string']);
        $reflection = new ReflectionClass($type);

        $properties = $reflection->getProperties();

        $this->assertParams(
            [
                'key' => [PrimitiveReflectionType::class, 'string'],
                'value' => [PrimitiveReflectionType::class, 'string'],
            ],
            $properties,
        );
    }

    #[Test] public function get_address_inputs(): void
    {
        $reflection = new ReflectionClass(Address::class);

        $properties = $reflection->getProperties();

        $this->assertParams(
            [
                'street' => [PrimitiveReflectionType::class, 'string'],
                'number' => [PrimitiveReflectionType::class, 'int'],
                'main' => [PrimitiveReflectionType::class, 'bool'],
                'place' => [ClassReflectionType::class, Place::class],
            ],
            $properties,
        );
    }

    #[Test] public function get_array_list_inputs(): void
    {
        $type = generic(ArrayList::class, [LatLng::class]);
        $reflection = new ReflectionClass($type);

        $properties = $reflection->getProperties();

        $this->assertParams(
            [
                'items' => [GenericReflectionType::class, generic('list', [LatLng::class])],
            ],
            $properties,
        );
    }

    #[Test] public function get_with_shape_inputs(): void
    {
        $reflection = new ReflectionClass(WithShape::class);
        $params = ['user' => User::class, 'address' => Address::class, 'description' => 'string'];

        $properties = $reflection->getProperties();

        $this->assertParams(
            [
                'data' => [ShapeReflectionType::class, shape('array', $params)],
                'payload' => [ShapeReflectionType::class, shape('object', $params)],
            ],
            $properties,
        );
    }

    #[Test] public function get_with_private_constructor_properties(): void
    {
        $reflection = new ReflectionClass(Company::class);

        $properties = $reflection->getProperties();

        $this->assertParams(
            [
                'businessName' => [PrimitiveReflectionType::class, 'string'],
                'registrationName' => [PrimitiveReflectionType::class, 'string'],
                'active' => [PrimitiveReflectionType::class, 'bool'],
                'addresses' => [GenericReflectionType::class, generic('list', [Address::class])],
            ],
            $properties,
        );
    }
}
