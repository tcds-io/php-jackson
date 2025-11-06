<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Jackson\Unit\Node\Runtime;

use PHPUnit\Framework\Attributes\Test;
use Test\Tcds\Io\Jackson\Fixture\ReadOnly\Address;
use Test\Tcds\Io\Jackson\Fixture\ReadOnly\LatLng;
use Tcds\Io\Jackson\Node\Runtime\RuntimeTypeNodeSpecificationFactory;
use Test\Tcds\Io\Jackson\SerializerTestCase;

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
