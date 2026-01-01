<?php

namespace Test\Tcds\Io\Jackson\Unit\Exception;

use PHPUnit\Framework\Attributes\Test;
use Tcds\Io\Jackson\Exception\UnableToParseValue;
use Tcds\Io\Jackson\JsonObjectMapper;
use Test\Tcds\Io\Jackson\Fixture\ReadOnly\Place;
use Test\Tcds\Io\Jackson\SerializerTestCase;

class ErrorHandlingTest extends SerializerTestCase
{
    #[Test] public function missing_root_value(): void
    {
        $mapper = new JsonObjectMapper();
        $json = <<<JSON
            {
              "country": "Brazil",
              "position": { "lat": "-26.9013", "lng": "-48.6655" }
            }
            JSON;

        /** @var UnableToParseValue $exception */
        $exception = $this->expectThrows(fn () => $mapper->readValue(Place::class, $json));

        $this->assertEquals('Unable to parse value', $exception->getMessage());
        $this->assertEquals([], $exception->path);
        $this->assertEquals(
            ['city' => 'string', 'country' => 'string', 'position' => ['lat' => 'float', 'lng' => 'float']],
            $exception->expected,
        );
        $this->assertEquals(
            ['country' => 'string', 'position' => ['lat' => 'float', 'lng' => 'float']],
            $exception->given,
        );
    }

    #[Test] public function missing_inner_value(): void
    {
        $mapper = new JsonObjectMapper();
        $json = <<<JSON
            {
              "city": "Itajaí",
              "country": "Brazil",
              "position": { "lat": "-26.9013" }
            }
            JSON;

        /** @var UnableToParseValue $exception */
        $exception = $this->expectThrows(fn () => $mapper->readValue(Place::class, $json));

        $this->assertEquals('Unable to parse value at .position', $exception->getMessage());
        $this->assertEquals(['position'], $exception->path);
        $this->assertEquals(['lat' => 'float', 'lng' => 'float'], $exception->expected);
        $this->assertEquals(['lat' => 'float'], $exception->given);
    }
}
