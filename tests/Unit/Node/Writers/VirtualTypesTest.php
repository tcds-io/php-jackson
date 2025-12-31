<?php

namespace Test\Tcds\Io\Jackson\Unit\Node\Writers;

use PHPUnit\Framework\Attributes\Test;
use Test\Tcds\Io\Jackson\Fixture\Pair;
use Test\Tcds\Io\Jackson\SerializerTestCase;

class VirtualTypesTest extends SerializerTestCase
{
    private const string JSON = <<<JSON
    {
      "key": "100",
      "value": {
        "foo": "bar"
      }
    }
    JSON;

    #[Test]
    public function numeric_string(): void
    {
        $this->assertEquals(
            new Pair('100', (object) ['foo' => 'bar']),
            $this->jsonMapper->readValue(
                type: generic(Pair::class, ['numeric-string', 'object{ foo: string }']),
                value: self::JSON,
            ),
        );
    }
}
