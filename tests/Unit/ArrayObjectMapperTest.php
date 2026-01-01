<?php

namespace Test\Tcds\Io\Jackson\Unit;

use PHPUnit\Framework\Attributes\Test;
use stdClass;
use Tcds\Io\Jackson\ArrayObjectMapper;
use Tcds\Io\Jackson\Exception\UnableToParseValue;
use Test\Tcds\Io\Jackson\Fixture\AccountType;
use Test\Tcds\Io\Jackson\Fixture\ReadOnly\AccountHolder;
use Test\Tcds\Io\Jackson\Fixture\ReadOnly\Address;
use Test\Tcds\Io\Jackson\Fixture\ReadOnly\LatLng;
use Test\Tcds\Io\Jackson\Fixture\ReadOnly\Response;
use Test\Tcds\Io\Jackson\SerializerTestCase;

class ArrayObjectMapperTest extends SerializerTestCase
{
    #[Test] public function given_a_json_then_read_value_into_the_object(): void
    {
        $data = json_decode(AccountHolder::json(), true);

        $accountHolder = $this->arrayMapper->readValueWith(AccountHolder::class, $data);

        $this->assertEquals(AccountHolder::thiagoCordeiro(), $accountHolder);
    }

    #[Test] public function given_a_json_then_parse_into_the_object_with_additional_content(): void
    {
        $partialData = json_decode(AccountHolder::partialJsonValue(), true);
        $accountHolder = $this->arrayMapper->readValueWith(AccountHolder::class, $partialData, [
            'name' => 'Thiago Cordeiro',
            'account' => [
                'number' => '12345-X',
                'type' => 'checking',
            ],
        ]);

        $this->assertEquals(AccountHolder::thiagoCordeiro(), $accountHolder);
    }

    #[Test] public function given_a_data_then_parse_into_the_object(): void
    {
        $data = AccountHolder::data();

        $accountHolder = $this->arrayMapper->readValue(AccountHolder::class, $data);

        $this->assertEquals(AccountHolder::thiagoCordeiro(), $accountHolder);
    }

    #[Test] public function given_a_invalid_value_then_throw_unable_to_parse(): void
    {
        $data = AccountHolder::data();
        $data['address']['place']['position'] = '-26.9013, -48.6655';

        /** @var UnableToParseValue $exception */
        $exception = $this->expectThrows(fn () => $this->arrayMapper->readValue(AccountHolder::class, $data));

        $this->assertEquals('Unable to parse value at .address.place.position', $exception->getMessage());
        $this->assertEquals(['address', 'place', 'position'], $exception->path);
        $this->assertEquals(['lat' => 'float', 'lng' => 'float'], $exception->expected);
        $this->assertEquals('string', $exception->given);
    }

    #[Test] public function given_custom_reader_then_parse_into_the_object(): void
    {
        $data = AccountHolder::data();
        $data['address']['place']['position'] = '-26.9013, -48.6655';

        $mapper = new ArrayObjectMapper(
            typeMappers: [
                LatLng::class => [
                    'reader' => fn (string $value) => new LatLng(...explode(',', $value)),
                ],
            ],
        );

        $accountHolder = $mapper->readValue(AccountHolder::class, $data);

        $this->assertEquals(AccountHolder::thiagoCordeiro(), $accountHolder);
    }

    #[Test] public function given_custom_reader_when_value_is_missing_then_run_custom_reader(): void
    {
        $data = AccountHolder::data();
        unset($data['address']['place']['position']);

        $mapper = new ArrayObjectMapper(
            typeMappers: [
                LatLng::class => [
                    'reader' => fn () => new LatLng(-26.9013, -48.6655),
                ],
            ],
        );

        $accountHolder = $mapper->readValue(AccountHolder::class, $data);

        $this->assertEquals(AccountHolder::thiagoCordeiro(), $accountHolder);
    }

    #[Test] public function given_an_object_with_map_param_then_handle_value(): void
    {
        $data = Response::data();

        $response = $this->arrayMapper->readValue(Response::class, $data);

        $this->assertEquals(Response::firstPage(), $response);
    }

    #[Test] public function given_a_map_array_then_handle_value(): void
    {
        $type = generic('map', ['string', Address::class]);

        $response = $this->arrayMapper->readValue($type, [
            'main' => Address::mainData(),
            'other' => Address::otherData(),
        ]);

        $this->assertEquals(
            [
                'main' => Address::main(),
                'other' => Address::other(),
            ],
            $response,
        );
    }

    #[Test] public function read_array_shape(): void
    {
        $type = shape('array', ['type' => AccountType::class, 'position' => LatLng::class]);

        $response = $this->arrayMapper->readValue($type, [
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

        $response = $this->arrayMapper->readValue($type, [
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
