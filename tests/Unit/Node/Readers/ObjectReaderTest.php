<?php

namespace Test\Tcds\Io\Jackson\Unit\Node\Readers;

use PHPUnit\Framework\Attributes\Test;
use Test\Tcds\Io\Jackson\Fixture\Pair;
use Test\Tcds\Io\Jackson\Fixture\ReadOnly\Address;
use Test\Tcds\Io\Jackson\SerializerTestCase;

class ObjectReaderTest extends SerializerTestCase
{
    #[Test] public function read_json_list_value(): void
    {
        $type = generic(Pair::class, ['string', Address::class]);
        $json = <<<JSON
            {
              "key": "other",
              "value": {
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
            }
            JSON;

        $pair = $this->jsonMapper->readValue($type, $json);

        $this->assertEquals(
            new Pair('other', Address::other()),
            $pair,
        );
    }
}
