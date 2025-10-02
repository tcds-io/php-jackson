<?php

namespace Tcds\Io\Serializer\Unit\Readers;

use PHPUnit\Framework\Attributes\Test;
use Tcds\Io\Generic\ArrayList;
use Tcds\Io\Serializer\Fixture\ReadOnly\Address;
use Tcds\Io\Serializer\JsonObjectMapper;
use Tcds\Io\Serializer\SerializerTestCase;

class ListReaderTest extends SerializerTestCase
{
    private JsonObjectMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new JsonObjectMapper();
    }

    #[Test] public function read_array_value(): void
    {
        $json = json_encode([Address::mainAddressData(), Address::otherAddressData()]);
        $type = generic('array', [Address::class]);

        $addresses = $this->mapper->readValue($type, $json);

        $this->assertEquals(
            [Address::mainAddress(), Address::otherAddress()],
            $addresses,
        );
    }

    #[Test] public function read_array_list_value(): void
    {
        $json = json_encode([Address::mainAddressData(), Address::otherAddressData()]);
        $type = generic(ArrayList::class, [Address::class]);

        /** @var ArrayList $addresses */
        $addresses = $this->mapper->readValue($type, $json);

        $this->assertEquals(
            [Address::mainAddress(), Address::otherAddress()],
            $addresses->items(),
        );
    }
}
