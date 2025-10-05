<?php

namespace Tcds\Io\Serializer\Unit;

use PHPUnit\Framework\Attributes\Test;
use stdClass;
use Tcds\Io\Serializer\ArrayObjectMapper;
use Tcds\Io\Serializer\Exception\UnableToParseValue;
use Tcds\Io\Serializer\Fixture\AccountType;
use Tcds\Io\Serializer\Fixture\ReadOnly\AccountHolder;
use Tcds\Io\Serializer\Fixture\ReadOnly\Address;
use Tcds\Io\Serializer\Fixture\ReadOnly\LatLng;
use Tcds\Io\Serializer\Fixture\ReadOnly\Response;
use Tcds\Io\Serializer\SerializerTestCase;

class ArrayObjectMapperTest extends SerializerTestCase
{
    private object $object;
    /** @var array<string, mixed> */
    private array $data;
    /** @var array<string, mixed> */
    private array $partialData;

    private ArrayObjectMapper $mapper;

    protected function setUp(): void
    {
        $this->object = AccountHolder::thiagoCordeiro();
        $this->data = json_decode(AccountHolder::json(), true);
        $this->partialData = json_decode(AccountHolder::partialJsonValue(), true);

        $this->mapper = new ArrayObjectMapper();
    }

    #[Test] public function given_custom_reader_then_parse_into_the_object(): void
    {
        $data = AccountHolder::data();
        $data['address']['place']['position'] = '-26.9013, -48.6655';

        $mapper = new ArrayObjectMapper(
            typeMappers: [
                LatLng::class => [
                    'reader' => fn(string $value) => new LatLng(...explode(',', $value)),
                ],
            ],
        );

        $accountHolder = $mapper->readValue(AccountHolder::class, $data);

        $this->assertEquals(AccountHolder::thiagoCordeiro(), $accountHolder);
    }

    #[Test] public function given_a_json_then_read_value_into_the_object(): void
    {
        $accountHolder = $this->mapper->readValueWith(AccountHolder::class, $this->data, []);

        $this->assertEquals($this->object, $accountHolder);
    }

    #[Test] public function given_a_json_then_parse_into_the_object_with_additional_content(): void
    {
        $accountHolder = $this->mapper->readValueWith(AccountHolder::class, $this->partialData, [
            'name' => 'Thiago Cordeiro',
            'account' => [
                'number' => '12345-X',
                'type' => 'checking',
            ],
        ]);

        $this->assertEquals($this->object, $accountHolder);
    }

    #[Test] public function given_a_data_then_parse_into_the_object(): void
    {
        $data = AccountHolder::data();

        $accountHolder = $this->mapper->readValue(AccountHolder::class, $data);

        $this->assertEquals(AccountHolder::thiagoCordeiro(), $accountHolder);
    }

    #[Test] public function given_a_invalid_value_then_throw_unable_to_parse(): void
    {
        $data = AccountHolder::data();
        $data['address']['place']['position'] = '-26.9013, -48.6655';

        $exception = $this->expectThrows(
            UnableToParseValue::class,
            fn() => $this->mapper->readValue(AccountHolder::class, $data),
        );

        $this->assertEquals(['address', 'place', 'position'], $exception->trace);
        $this->assertEquals(['lat' => 'float', 'lng' => 'float'], $exception->expected);
        $this->assertEquals('string', $exception->given);
    }

    #[Test] public function given_an_object_with_map_param_then_handle_value(): void
    {
        $data = Response::data();

        $response = $this->mapper->readValue(Response::class, $data);

        $this->assertEquals(Response::firstPage(), $response);
    }

    #[Test] public function given_a_map_array_then_handle_value(): void
    {
        $type = generic('map', ['string', Address::class]);

        $response = $this->mapper->readValue($type, [
            'main' => Address::mainAddressData(),
            'other' => Address::otherAddressData(),
        ]);

        $this->assertEquals(
            [
                'main' => Address::mainAddress(),
                'other' => Address::otherAddress(),
            ],
            $response,
        );
    }

    #[Test] public function read_array_shape(): void
    {
        $type = shape('array', ['type' => AccountType::class, 'position' => LatLng::class]);

        $response = $this->mapper->readValue($type, [
            'type' => 'checking',
            'position' => [
                'lat' => '-26.9013',
                'lng' => '-48.6655',
            ],
        ]);

        $this->assertEquals(
            [
                'type' => AccountType::CHECKING,
                'position' => new LatLng(
                    lat: -26.9013,
                    lng: -48.6655,
                ),
            ],
            $response,
        );
    }

    #[Test] public function read_object_shape(): void
    {
        $type = shape('object', ['type' => AccountType::class, 'position' => LatLng::class]);

        $response = $this->mapper->readValue($type, [
            'type' => 'checking',
            'position' => [
                'lat' => '-26.9013',
                'lng' => '-48.6655',
            ],
        ]);

        $object = new stdClass();
        $object->type = AccountType::CHECKING;
        $object->position = new LatLng(lat: -26.9013, lng: -48.6655);

        $this->assertEquals($object, $response);
    }
}
