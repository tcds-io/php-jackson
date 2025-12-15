<?php

namespace Test\Tcds\Io\Jackson\Unit\Node\Writers;

use PHPUnit\Framework\Attributes\Test;
use Test\Tcds\Io\Jackson\Fixture\DataPayloadShape;
use Test\Tcds\Io\Jackson\Fixture\ReadOnly\Address;
use Test\Tcds\Io\Jackson\Fixture\ReadOnly\User;
use Test\Tcds\Io\Jackson\SerializerTestCase;

class ShapeTypeWriterTest extends SerializerTestCase
{
    private const string JSON = <<<JSON
        {
          "data": {
            "user": {
              "name": "Arthur Dent",
              "age": 27,
              "height": 1.77,
              "address": {
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
            },
            "address": {
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
            },
            "description": "Array shaped"
          },
          "payload": {
            "user": {
              "name": "Arthur Dent",
              "age": 27,
              "height": 1.77,
              "address": {
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
            },
            "address": {
              "street": "street street",
              "number": 100,
              "main": false,
              "place": {
                "city": "São Paulo",
                "country": "Brazil",
                "position": {
                  "lat": -26.9013,
                  "lng": -48.6655
                }
              }
            },
            "description": "Object shaped"
          }
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

        $this->assertEquals(
            [
                'data' => [
                    'user' => User::arthurDentData(),
                    'address' => Address::mainData(),
                    'description' => 'Array shaped',
                ],
                'payload' => [
                    'user' => User::arthurDentData(),
                    'address' => Address::otherData(),
                    'description' => 'Object shaped',
                ],
            ],
            $this->arrayMapper->writeValue($object),
        );
        $this->assertJsonStringEqualsJsonString(self::JSON, $this->jsonMapper->writeValue($object));
    }
}
