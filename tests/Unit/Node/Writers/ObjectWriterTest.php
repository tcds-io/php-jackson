<?php

namespace Test\Tcds\Io\Jackson\Unit\Node\Writers;

use PHPUnit\Framework\Attributes\Test;
use Test\Tcds\Io\Jackson\Fixture\ReadOnly\Address;
use Test\Tcds\Io\Jackson\SerializerTestCase;

class ObjectWriterTest extends SerializerTestCase
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
        "street": "main street",
        "number": 150,
        "main": true,
        "place": {
            "city": "Santa Catarina",
            "country": "Brazil",
            "position": {
                "lat": -26.9013,
                "lng": -48.6655
            }
        }
    }
    JSON;

    #[Test] public function write_object_value(): void
    {
        $object = Address::main();

        $this->assertEquals(self::ARRAY, $this->arrayMapper->writeValue($object));
        $this->assertJsonStringEqualsJsonString(self::JSON, $this->jsonMapper->writeValue($object));
    }
}
