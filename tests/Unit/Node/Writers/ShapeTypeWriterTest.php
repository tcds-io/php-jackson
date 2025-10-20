<?php

namespace Tcds\Io\Serializer\Unit\Node\Writers;

use PHPUnit\Framework\Attributes\Test;
use Tcds\Io\Serializer\Fixture\DataPayloadShape;
use Tcds\Io\Serializer\Fixture\ReadOnly\Address;
use Tcds\Io\Serializer\Fixture\ReadOnly\User;
use Tcds\Io\Serializer\SerializerTestCase;

class ShapeTypeWriterTest extends SerializerTestCase
{
    private const array ARRAY = [
        'main' => true,
        'number' => 150,
        'place' => [
            'city' => 'Santa Catarina',
            'country' => 'Brazil',
            'position' => [
                'lat' => -26.9013,
                'lng' => -48.6655,
            ],
        ],
        'street' => 'main street',
    ];

    private const string JSON = <<<JSON
    {
        "main": true,
        "number": 150,
        "place": {
            "city": "Santa Catarina",
            "country": "Brazil",
            "position": {
                "lat": -26.9013,
                "lng": -48.6655
            }
        },
        "street": "main street"
    }
    JSON;

    #[Test] public function write_array_shape(): void
    {
        $object = new DataPayloadShape(
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

        $this->assertEquals(self::ARRAY, $this->arrayMapper->writeValue($object));
        $this->assertJsonStringEqualsJsonString(self::JSON, $this->jsonMapper->writeValue($object));
    }
}
