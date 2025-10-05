<?php

namespace Tcds\Io\Serializer\Unit\Writers;

use PHPUnit\Framework\Attributes\Test;
use Tcds\Io\Serializer\Fixture\ReadOnly\Address;
use Tcds\Io\Serializer\SerializerTestCase;

class ObjectWriterTest extends SerializerTestCase
{
    private const string MAIN_ADDRESS_OUTPUT = <<<JSON
    {
      "street":  "",
      "number": "",
      "main":  "",
      "place": {
        "city": "",
        "country":"",
        "position": {
          "lat": "",
          "lng": ""
        }
      }
    }
    JSON;

    #[Test] public function write_object_value(): void
    {
        $object = Address::main();

        $output = $this->jsonMapper->writeValue($object);

        $this->assertEquals(self::MAIN_ADDRESS_OUTPUT, $output);
    }
}
