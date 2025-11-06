<?php

namespace Test\Tcds\Io\Jackson\Unit;

use PHPUnit\Framework\Attributes\Test;
use Test\Tcds\Io\Jackson\Fixture\ReadOnly\LatLng;
use Test\Tcds\Io\Jackson\Fixture\ReadOnly\Place;
use Tcds\Io\Jackson\JsonObjectMapper;
use Test\Tcds\Io\Jackson\SerializerTestCase;

class ReadmeTest extends SerializerTestCase
{
    #[Test] public function place(): void
    {
        $mapper = new JsonObjectMapper();
        $jsonPlace = <<<JSON
        {
          "city": "Itajaí",
          "country": "Brazil",
          "position": { "lat": "-26.9013", "lng": "-48.6655" }
        }
        JSON;

        $place = $mapper->readValue(Place::class, $jsonPlace);

        $this->assertEquals(
            new Place(
                city: 'Itajaí',
                country: 'Brazil',
                position: new LatLng(lat: -26.9013, lng: -48.6655),
            ),
            $place,
        );
    }

    #[Test] public function generic(): void
    {
        $mapper = new JsonObjectMapper();
        $jsonPositions = <<<JSON
        [
          { "lat": "-26.9013", "lng": "-48.6655" },
          { "lat": "-27.1234", "lng": "-49.5678" }
        ]
        JSON;

        $positions = $mapper->readValue('list<Test\Tcds\Io\Jackson\Fixture\ReadOnly\LatLng>', $jsonPositions);

        $this->assertEquals(
            [
                new LatLng(lat: -26.9013, lng: -48.6655),
                new LatLng(lat: -27.1234, lng: -49.5678),
            ],
            $positions,
        );
    }
}
