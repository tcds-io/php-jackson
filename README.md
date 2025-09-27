# PHP-Jackson Serializer

###### A lightweight, flexible object serializer for PHP, inspired by [Jackson](https://github.com/FasterXML/jackson).

### Key Features

- 🔄 Serialization & Deserialization – Convert PHP objects to JSON and reconstruct them back.
- 📝 Annotation & Attribute Support – Control how fields are named, included, or ignored.
- ⚙️ Custom Mappers – Define custom serializers/deserializers for specific types.
- 🧩 Extensible – Plug in modules to extend functionality (date handling, enums, etc.).
- 🎯 Type-Safe – Handles nested objects, arrays, and generics-like collections.
- ⚡ Lightweight & Performant – Built for modern PHP without unnecessary overhead.

### Usage

```php
<?php

readonly class LatLng
{
    public function __construct(
        public float $lat,
        public float $lng,
    ) {}
}

readonly class Place
{
    public function __construct(
        public string $city,
        public string $country,
        public LatLng $position,
    ) {}
}

use Tcds\Io\Serializer\JsonObjectMapper;

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
```

### Generics
```php
$jsonPositions = <<<JSON
[
  { "lat": "-26.9013", "lng": "-48.6655" },
  { "lat": "-27.1234", "lng": "-49.5678" }
]
JSON;

$positions = $mapper->readValue('list<LatLng>', $jsonPositions);
// or
$positions = $mapper->readValue(generic('list', LatLng::class), $jsonPositions);

$this->assertEquals(
    [
        new LatLng(lat: -26.9013, lng: -48.6655),
        new LatLng(lat: -27.1234, lng: -49.5678),
    ],
    $positions,
);
```
