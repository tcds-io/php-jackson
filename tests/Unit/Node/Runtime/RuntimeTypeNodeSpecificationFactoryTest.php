<?php

declare(strict_types=1);

namespace Tcds\Io\Serializer\Unit\Node\Runtime;

use PHPUnit\Framework\Attributes\Test;
use Tcds\Io\Serializer\Fixture\ReadOnly\Address;
use Tcds\Io\Serializer\Fixture\ReadOnly\LatLng;
use Tcds\Io\Serializer\Node\Runtime\RuntimeTypeNodeSpecificationFactory;
use Tcds\Io\Serializer\SerializerTestCase;

class RuntimeTypeNodeSpecificationFactoryTest extends SerializerTestCase
{
    #[Test]
    public function primitive(): void
    {
        $factory = new RuntimeTypeNodeSpecificationFactory();

        $this->assertEquals('int', $factory->create('int'));
        $this->assertEquals('integer', $factory->create('integer'));
        $this->assertEquals('float', $factory->create('float'));
        $this->assertEquals('double', $factory->create('double'));
        $this->assertEquals('bool', $factory->create('bool'));
        $this->assertEquals('boolean', $factory->create('boolean'));
    }

    #[Test]
    public function object_shape_type(): void
    {
        $factory = new RuntimeTypeNodeSpecificationFactory();
        $type = shape('object', [
            'int' => 'int',
            'float' => 'float',
            'string' => 'string',
            'bool_true' => 'bool',
            'bool_false' => 'bool',
            'lat_lng' => LatLng::class,
        ]);

        $specification = $factory->create($type);

        $this->assertEquals(
            [
                'int' => 'int',
                'float' => 'float',
                'string' => 'string',
                'bool_true' => 'bool',
                'bool_false' => 'bool',
                'lat_lng' => ['lat' => 'float', 'lng' => 'float'],
            ],
            $specification,
        );
    }

    #[Test]
    public function array_shape_type(): void
    {
        $factory = new RuntimeTypeNodeSpecificationFactory();
        $type = shape('array', ['address' => Address::class]);

        $specification = $factory->create($type);

        $this->assertEquals(
            [
                'address' => [
                    'street' => 'string',
                    'number' => 'int',
                    'main' => 'bool',
                    'place' => [
                        'city' => 'string',
                        'country' => 'string',
                        'position' => ['lat' => 'float', 'lng' => 'float'],
                    ],
                ],
            ],
            $specification,
        );
    }

    #[Test]
    public function object(): void
    {
        $factory = new RuntimeTypeNodeSpecificationFactory();
        $type = Address::class;

        $specification = $factory->create($type);

        $this->assertEquals(
            [
                'street' => 'string',
                'number' => 'int',
                'main' => 'bool',
                'place' => [
                    'city' => 'string',
                    'country' => 'string',
                    'position' => ['lat' => 'float', 'lng' => 'float'],
                ],
            ],
            $specification,
        );
    }
}
