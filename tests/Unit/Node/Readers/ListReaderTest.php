<?php

namespace Tcds\Io\Serializer\Unit\Node\Readers;

use PHPUnit\Framework\Attributes\Test;
use Tcds\Io\Generic\ArrayList;
use Tcds\Io\Serializer\Fixture\ReadOnly\Address;
use Tcds\Io\Serializer\SerializerTestCase;

class ListReaderTest extends SerializerTestCase
{
    private const array ARRAY = [
        [
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
        ],
        [
            'street' => 'street street',
            'number' => '100',
            'main' => 'false',
            'place' => [
                'city' => 'São Paulo',
                'country' => 'Brazil',
                'position' => [
                    'lat' => '-26.9013',
                    'lng' => '-48.6655',
                ],
            ],
        ],
    ];

    private const string JSON = <<<JSON
    [
        {
            "street": "main street",
            "main": true,
            "number": 150,
            "place": {
                "city": "Santa Catarina",
                "country": "Brazil",
                "position": {
                    "lat": -26.9013,
                    "lng": -48.6655
                }
            }
        },
        {
          "street": "street street",
          "main": "false",
          "number": "100",
          "place": {
            "city": "São Paulo",
            "country": "Brazil",
            "position": {
              "lat": "-26.9013",
              "lng": "-48.6655"
            }
          }
        }
    ]
    JSON;

    #[Test] public function read_json_list_value(): void
    {
        $type = generic('list', [Address::class]);

        $addresses = $this->jsonMapper->readValue($type, self::JSON);

        $this->assertEquals(
            [Address::main(), Address::other()],
            $addresses,
        );
    }

    #[Test] public function read_json_array_list_value(): void
    {
        $type = generic(ArrayList::class, [Address::class]);

        /** @var ArrayList $addresses */
        $addresses = $this->jsonMapper->readValue($type, self::JSON);

        $this->assertEquals(
            [Address::main(), Address::other()],
            $addresses->items(),
        );
    }

    #[Test] public function read_array_value(): void
    {
        $type = generic('list', [Address::class]);

        $addresses = $this->arrayMapper->readValue($type, self::ARRAY);

        $this->assertEquals(
            [Address::main(), Address::other()],
            $addresses,
        );
    }

    #[Test] public function read_array_list_value(): void
    {
        $type = generic(ArrayList::class, [Address::class]);

        /** @var ArrayList $addresses */
        $addresses = $this->arrayMapper->readValue($type, self::ARRAY);

        $this->assertEquals(
            [Address::main(), Address::other()],
            $addresses->items(),
        );
    }

//    #[Test] public function read_json_list_with_generic_value(): void
//    {
//        $type = generic('list', [generic(Pair::class, ['string', Address::class])]);
//        $json = <<<JSON
//        [
//            {
//              "key": "other",
//              "value": {
//                "street": "street street",
//                "main": "false",
//                "number": "100",
//                "place": {
//                  "city": "São Paulo",
//                  "country": "Brazil",
//                  "position": {
//                    "lat": "-26.9013",
//                    "lng": "-48.6655"
//                  }
//                }
//              }
//            }
//        ]
//        JSON;
//
//        $pairs = $this->jsonMapper->readValue($type, $json);
//
//        $this->assertEquals(
//            [new Pair('other', Address::other())],
//            $pairs,
//        );
//    }
}
