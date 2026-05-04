# PHP-Jackson

[![PHP Tests](https://github.com/tcds-io/php-jackson/actions/workflows/tests.yml/badge.svg)](https://github.com/tcds-io/php-jackson/actions/workflows/tests.yml)

###### A lightweight, flexible object serializer for PHP, inspired by [Jackson](https://github.com/FasterXML/jackson).

It provides strong typing, JSON ↔ object mapping, generics support, array/object shapes, custom type mappers, and detailed error tracing.

## 📚 Contents

- [Overview](#overview)
- [Integrations](#-integrations)
    - <a href="https://github.com/tcds-io/php-jackson-laravel" target="_blank" rel="noopener noreferrer">Laravel <small>↗</small></a>
    - <a href="https://github.com/tcds-io/php-jackson-symfony" target="_blank" rel="noopener noreferrer">Symfony <small>↗</small></a>
    - <a href="https://github.com/tcds-io/php-jackson-guzzle" target="_blank" rel="noopener noreferrer">Guzzle <small>↗</small></a>
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
- [Renaming JSON keys with `#[JsonProperty]`](#-renaming-json-keys-with-jsonproperty)
- [Custom Type Mappers](#-custom-type-mappers)
    - [Using Custom Mappers with External Context](#using-custom-mappers-with-external-context)
    - [Pinning a Mapper on the Class with `#[JsonMapper]`](#pinning-a-mapper-on-the-class-with-jsonmapper)
- [Date Handling](#-date-handling)
- [Error Handling](#-error-handling)
- [Development](#-development)
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

## 🧩 Integrations

PHP Jackson offers first-class integrations for popular PHP frameworks and tools.
Each integration extends the core mapper with framework-specific features for a smoother development experience.

Official Plugins:
- <a href="https://github.com/tcds-io/php-jackson-laravel" target="_blank" rel="noopener noreferrer">Laravel <small>↗</small></a> — controller injection, JSON responses, request error handling, and Eloquent casts
- <a href="https://github.com/tcds-io/php-jackson-symfony" target="_blank" rel="noopener noreferrer">Symfony <small>↗</small></a> — controller argument resolvers, JSON responses, and configurable request error handling
- <a href="https://github.com/tcds-io/php-jackson-guzzle" target="_blank" rel="noopener noreferrer">Guzzle <small>↗</small></a> — typed HTTP client with request DTO mapping and async response parsing

## 🔧 Basic Usage

### Reading JSON into typed objects

```php
use Tcds\Io\Jackson\JsonObjectMapper;

$mapper = new JsonObjectMapper();

$address = $mapper->readValue(Address::class, $json);
```

Equivalent array version:

```php
use Tcds\Io\Jackson\ArrayObjectMapper;

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

The `generic()` and `shape()` helper functions are loaded by Composer through `php-better-generics`.

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

## 🏷️ Renaming JSON keys with `#[JsonProperty]`

PHP-Jackson maps JSON keys to PHP names 1:1 by default. When the wire format
uses a different naming convention (snake_case, kebab-case, etc.), pin the
JSON key on the constructor parameter (or property) with `#[JsonProperty]`:

```php
use Tcds\Io\Jackson\Node\JsonProperty;

readonly class User
{
    public function __construct(
        #[JsonProperty('first_name')] public string $firstName,
        #[JsonProperty('last_name')] public string $lastName,
        public int $age,
    ) {}
}
```

The attribute is honored on **both** directions:

```php
$mapper = new JsonObjectMapper();

$user = $mapper->readValue(User::class, '{"first_name":"Arthur","last_name":"Dent","age":42}');
// User { firstName: "Arthur", lastName: "Dent", age: 42 }

$mapper->writeValue($user);
// {"first_name":"Arthur","last_name":"Dent","age":42}
```

Error traces and the `expected` payload on `UnableToParseValue` use the wire
key — the one users will recognize from the JSON they are sending — not the
PHP identifier.

---

## 🧩 Custom Type Mappers

Custom mappers are useful when object construction depends on complex logic or external data:

```php
use Tcds\Io\Jackson\ArrayObjectMapper;

$mapper = new ArrayObjectMapper(
    typeMappers: [
        LatLng::class => [
            'reader' => fn(string $data) => new LatLng(...explode(',', $data)),
            'writer' => fn(LatLng $data) => sprintf("%s, %s", $data->lat, $data->lng),
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
use Tcds\Io\Jackson\ArrayObjectMapper;

$mapper = new ArrayObjectMapper(
    typeMappers: [
        User::class => [
            'reader' => fn() => Auth::user(),
            'writer' => fn(User $data) => [
                'id' => $data->id,
                'name' => $data->name,
                // 'email' intentionally omitted
            ],
        ]
    ]
);
```

Mapper closures can receive any of the named arguments used internally by PHP-Jackson:

```php
fn(mixed $data, string $type, ObjectMapper $mapper, array $path): mixed
```

Use only the parameters you need; `ReflectionFunction::call()` binds them by name.

---

### Pinning a Mapper on the Class with `#[JsonMapper]`

If a class always wants the same custom (de)serialization, declare it once on
the class itself instead of registering it on every mapper instance:

```php
use Tcds\Io\Jackson\Node\JsonMapper;

#[JsonMapper(reader: MoneyReader::class, writer: MoneyWriter::class)]
readonly class Money
{
    public function __construct(public int $cents) {}
}
```

The `reader` and `writer` accept any of:

- a class string of an implementation of `Reader` / `Writer` (instance is
  built with a no-arg constructor),
- a class string of `StaticReader` / `StaticWriter` (no instance — the static
  `read` / `write` is called),
- a class string of any class with a matching `__invoke` (treated as a
  `MapperClosure`),
- an instance of `Reader` / `Writer` (PHP 8.1 `new` in attribute initializers),
- a `Closure` matching `MapperClosure`, when constructing `JsonMapper`
  programmatically (PHP attribute literals can't carry closures).

**Resolution order on every read/write:**

1. `#[JsonMapper]` attribute on the target class — declaration site, wins
2. `typeMappers` constructor argument
3. default reader/writer

That is, an explicit class-level mapper cannot be silently overridden by
mapper-instance config — the class itself is the canonical source.

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

## 🔧 Development

```bash
composer install
composer tests       # runs cs:check + phpstan + phpunit
composer cs:fix      # auto-fix code style
```

---

## ✅ Summary

You can:

- Read JSON → typed objects via `JsonObjectMapper`
- Read arrays → typed objects via `ArrayObjectMapper`
- Merge missing fields using `readValueWith`
- Write objects → JSON/arrays via `writeValue`
- Use generics (`list<T>`, `map<K,V>`, shapes)
- Rename wire keys per field with `#[JsonProperty('snake_case')]`
- Register custom mappers for any class via `typeMappers` or pin them on the
  class itself with `#[JsonMapper(reader: …, writer: …)]`
- Rely on strong error tracing with full path information
