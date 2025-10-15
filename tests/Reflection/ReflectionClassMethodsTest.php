<?php

declare(strict_types=1);

namespace Tcds\Io\Serializer\Reflection;

use PHPUnit\Framework\Attributes\Test;
use Tcds\Io\Serializer\Fixture\Pair;
use Tcds\Io\Serializer\Fixture\ReadOnly\Address;
use Tcds\Io\Serializer\Fixture\ReadOnly\Company;
use Tcds\Io\Serializer\Fixture\ReadOnly\LatLng;
use Tcds\Io\Serializer\Fixture\ReadOnly\User;
use Tcds\Io\Serializer\Fixture\WithShape;
use Tcds\Io\Serializer\Reflection\Type\ClassReflectionType;
use Tcds\Io\Serializer\Reflection\Type\GenericReflectionType;
use Tcds\Io\Serializer\Reflection\Type\PrimitiveReflectionType;
use Tcds\Io\Serializer\Reflection\Type\ShapeReflectionType;
use Tcds\Io\Serializer\SerializerTestCase;

class ReflectionClassMethodsTest extends SerializerTestCase
{
    #[Test] public function given_a_class_then_get_its_constructor_params(): void
    {
        $reflection = new ReflectionClass(Company::class);
        $method = $reflection->getConstructor();

        $params = $method->getParameters();

        $this->assertParams(
            [
                'businessName' => [PrimitiveReflectionType::class, 'string'],
                'registrationName' => [PrimitiveReflectionType::class, 'string'],
                'active' => [PrimitiveReflectionType::class, 'bool'],
                'addresses' => [GenericReflectionType::class, generic('list', [Address::class])],
            ],
            $params,
        );
    }

    #[Test] public function given_a_static_method_then_get_its_params(): void
    {
        $reflection = new ReflectionClass(Company::class);
        $method = $reflection->getMethod('create');

        $params = $method->getParameters();

        $this->assertParams(
            [
                'name' => [PrimitiveReflectionType::class, 'string'],
                'active' => [PrimitiveReflectionType::class, 'bool'],
                'addresses' => [GenericReflectionType::class, generic('list', [Address::class])],
            ],
            $params,
        );
    }

    #[Test] public function get_generic_return_type(): void
    {
        $reflection = new ReflectionClass(Company::class);
        $method = $reflection->getMethod('getAddresses');

        $type = $method->getReturnType();

        $this->assertEquals(new GenericReflectionType($reflection, 'list', [Address::class]), $type);
    }

    #[Test] public function get_template_return_type(): void
    {
        $reflection = new ReflectionClass(generic(Pair::class, ['string', LatLng::class]));

        $keyType = $reflection->getMethod('key')->getReturnType();
        $valueType = $reflection->getMethod('value')->getReturnType();

        $this->assertEquals(new PrimitiveReflectionType($reflection, 'string'), $keyType);
        $this->assertEquals(new ClassReflectionType($reflection, LatLng::class), $valueType);
    }

    #[Test] public function get_method_annotated_return_type(): void
    {
        $reflection = new ReflectionClass(WithShape::class);
        $params = [
            'user' => User::class,
            'address' => Address::class,
            'description' => 'string',
        ];

        $getData = $reflection->getMethod('getData');
        $getPayload = $reflection->getMethod('getPayload');

        $this->assertEquals(new ShapeReflectionType($reflection, 'array', $params), $getData->getReturnType());
        $this->assertEquals(new ShapeReflectionType($reflection, 'object', $params), $getPayload->getReturnType());
    }
}
