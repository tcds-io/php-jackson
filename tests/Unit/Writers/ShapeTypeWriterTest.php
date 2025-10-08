<?php

namespace Tcds\Io\Serializer\Unit\Writers;

use PHPUnit\Framework\Attributes\Test;
use Tcds\Io\Serializer\Fixture\ReadOnly\Address;
use Tcds\Io\Serializer\Fixture\ReadOnly\User;
use Tcds\Io\Serializer\Fixture\WithShape;
use Tcds\Io\Serializer\JsonObjectMapper;
use Tcds\Io\Serializer\ObjectMapper;
use Tcds\Io\Serializer\SerializerTestCase;

class ShapeTypeWriterTest extends SerializerTestCase
{
    private ObjectMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new JsonObjectMapper();
    }

    #[Test] public function write_array_shape(): void
    {
        $object = new WithShape(
            data: [
                'user' => User::arthurDent(),
                'address' => Address::main(),
                'description' => 'Array shaped',
            ],
            payload: (object) [
                'user' => User::arthurDent(),
                'address' => Address::other(),
                'description' => 'Object shaped',
            ],
        );

        $mapped = $this->mapper->writeValue($object);
    }
}
