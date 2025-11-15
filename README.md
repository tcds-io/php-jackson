# PHP-Jackson

###### A lightweight, flexible object serializer for PHP, inspired by [Jackson](https://github.com/FasterXML/jackson).

It provides strong typing, JSON ↔ object mapping, generics support, array/object shapes, custom type mappers, and detailed error tracing.

## 📚 Contents

- [Overview](#overview)
- [Installation](#-installation)
- [Basic Usage](#-basic-usage)
- [Deserializing from JSON](#-deserializing-from-json)
- [Merging Additional Data (`readValueWith`)](#-merging-additional-data-readvaluewith)
- [Serializing Objects](#-serializing-objects)
- [Generic Types (`list<t>  map<k, v>  shapes`)](#-generic-types-listt-mapkv-shapes)
    - [List Example](#list-example)
    - [Map Example](#map-example)
    - [Array Shape Example](#array-shape-example)
    - [Object Shape Example](#object-shape-example)
- [Custom Type Mappers](#-custom-type-mappers)
- [Date Handling](#-date-handling)
- [Error Handling](#-error-handling)
- [Summary](#-summary)

---

## Overview

Main components:

- **JsonObjectMapper** — handles JSON strings at the boundary.
- **ArrayObjectMapper** — handles associative arrays at the boundary.
- **Type Mappers** — custom readers/writers for specific classes.
- **Generic Types** — support for `list<T>`, `map<K,V>`, shapes, etc.
- **Date Handling** — built-in support for DateTime and Carbon.
- **Error Reporting** — typed exceptions with full trace paths.

---

## 🚀 Installation

```bash
composer require tcds-io/php-jackson
```

Namespaces:

```php
use Tcds\Io\Jackson\JsonObjectMapper;
use Tcds\Io\Jackson\ArrayObjectMapper;
```

---

## 🔧 Basic Usage

### Reading JSON into typed objects

```php
$mapper = new JsonObjectMapper();

$address = $mapper->readValue(Address::class, $json);
```

Equivalent array version:

```php
$mapper = new ArrayObjectMapper();
$address = $mapper->readValue(Address::class, $dataArray);
```

---

## 📥 Deserializing from JSON

```php
$json = <<<JSON
{
    "street": "Ocean avenue",
    "number": "100",
    "main": "true",
    "place": {
        "city": "Rio de Janeiro",
        "country": "Brazil",
        "position": { "lat": "-26.9013", "lng": "-48.6655" }
    }
}
JSON;

$mapper = new JsonObjectMapper();

$address = $mapper->readValue(Address::class, $json);
```

The resulting object matches:

```php
new Address(
    street: 'Ocean avenue',
    number: 100,
    main: true,
    place: new Place(
        city: 'Rio de Janeiro',
        country: 'Brazil',
        position: new LatLng(lat: -26.9013, lng: -48.6655),
    ),
);
```

---

## ➕ Merging Additional Data (`readValueWith`)

Merging data is useful when the incoming payload does not contain all required values and those values must be completed from another source:

```php
$partial = <<<JSON
{
    "street": "Ocean avenue",
    "number": "100",
    "main": "true"
}
JSON;

$address = $mapper->readValueWith(
    Address::class,
    $partial,
    [
        'place' => [
            'city' => "Rio de Janeiro",
            'country' => "Brazil",
            'position' => [
                'lat' => -26.9013,
                'lng' => -48.6655,
            ]
        ]
    ]
);
```

---

## 📤 Serializing Objects

Array output:

```php
$mapper = new ArrayObjectMapper();
$array = $mapper->writeValue($object);
```

JSON output:

```php
$mapper = new JsonObjectMapper();
$json = $mapper->writeValue($object);
```

---

## 📚 Generic Types (`list<T>`, `map<K,V>`, shapes)

### List example

```php
$list = $mapper->readValue('list<LatLng>', $json);
```

Using `generic()`:

```php
$type = generic('list', [LatLng::class]);

$list = $mapper->readValue($type, $json);
```

---

### Map example

```php
$type = generic('map', ['string', Address::class]);

$result = $mapper->readValue($type, [
    'main'  => Address::mainData(),
    'other' => Address::otherData(),
]);
```

---

### Array Shape Example

```php
$type = shape('array', [
    'type'     => AccountType::class,
    'position' => LatLng::class,
]);
```

Produces:

```php
[
  'type' => AccountType::CHECKING,
  'position' => new LatLng(...),
]
```

---

### Object Shape Example

```php
$type = shape('object', [
    'type'     => AccountType::class,
    'position' => LatLng::class
]);
```

Produces a `stdClass`:

```php
$object->type     === AccountType::CHECKING
$object->position instanceof LatLng
```

---

## 🧩 Custom Type Mappers

Custom mappers are useful when object construction depends on complex logic or external data:

```php
$mapper = new ArrayObjectMapper(
    typeMappers: [
        LatLng::class => [
            'reader' => fn (string $value) => new LatLng(...explode(',', $value)),
            'writer' => fn (LatLng $value) => sprintf("%s, %s", $value->lat, $value->lng),
        ]
    ]
);
```

This allows:

```php
"position" => "-26.9013, -48.6655"
```

to become:

```php
new LatLng(-26.9013, -48.6655)
```

and serialize back into:

```php
"position" => "-26.9013, -48.6655"
```

---

### Using Custom Mappers with External Context

```php
$mapper = new ArrayObjectMapper(
    typeMappers: [
        User::class => [
            'reader' => fn () => Auth::user(),
            'writer' => fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                // 'email' intentionally omitted
            ],
        ]
    ]
);
```

---

## 🕒 Date Handling

PHP-Jackson provides built-in support for:

- DateTime
- DateTimeImmutable
- Carbon
- CarbonImmutable
- DateTimeInterface

Dates are serialized and deserialized using ISO-8601 strings:

```php
[
  'datetime' => '2025-10-22T11:21:31+00:00'
]
```

---

## ❗ Error Handling

When parsing fails, the library throws:

### `UnableToParseValue`

Properties:

```php
$e->trace;     // ['address','place','position']
$e->expected;  // expected type description
$e->given;     // actual given value
```

Example message:

```
Unable to parse value at .address.place.position
```

This makes debugging extremely easy.

---

## ✅ Summary

You can:

- Read JSON → typed objects via `JsonObjectMapper`
- Read arrays → typed objects via `ArrayObjectMapper`
- Merge missing fields using `readValueWith`
- Write objects → JSON/arrays via `writeValue`
- Use generics (`list<T>`, `map<K,V>`, shapes)
- Register custom mappers for any class
- Rely on strong error tracing with full path information
