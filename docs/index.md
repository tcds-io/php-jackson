---
layout: page
title: "PHP-Jackson Documentation"
---

# PHP-Jackson – Full Documentation

This documentation is generated based on the actual test suite and source code of the `php-jackson` project.  
It is ready to be placed inside a **GitHub Pages `docs/` folder**.

---

## 📌 Overview

    PHP‑Jackson is an object‑mapper inspired by Jackson (Java).  
It provides strong typing, JSON ↔ object mapping, generics support, array/object shapes, custom type mappers, and detailed error tracing.

Main components:

- **JsonObjectMapper** — handles JSON strings at the boundary.
- **ArrayObjectMapper** — handles associative arrays at the boundary.
- **Type Mappers** — custom readers/writers for specific classes.
- **Generic Types** — support for `list<T>`, `map<K,V>`, shapes, etc.
- **Date Handling** — built-in support for DateTime, Carbon, etc.
- **Error Reporting** — typed exceptions with full trace path.

---

# 🚀 Installation

```bash
composer require tcds-io/php-jackson
```

Namespaces:

```php
use Tcds\Io\Jackson\JsonObjectMapper;
use Tcds\Io\Jackson\ArrayObjectMapper;
```

---

# 🔧 Basic Usage

## Reading JSON into Typed Objects

```php
$mapper = new JsonObjectMapper();

/** @var AccountHolder $holder */
$holder = $mapper->readValue(AccountHolder::class, $json);
```

Equivalent array version:

```php
$mapper = new ArrayObjectMapper();
$holder = $mapper->readValue(AccountHolder::class, $dataArray);
```

---

# 📥 Deserializing from JSON

Example derived from the real tests:

```php
$json = AccountHolder::json();

$mapper = new JsonObjectMapper();

/** @var AccountHolder $result */
$result = $mapper->readValue(AccountHolder::class, $json);
```

The resulting object matches:

```php
AccountHolder::thiagoCordeiro();
```

---

# ➕ Merging Additional Data (readValueWith)

From tests:

```php
$partial = AccountHolder::partialJsonValue();

$holder = $mapper->readValueWith(
    AccountHolder::class,
    $partial,
    [
        'name' => 'Thiago Cordeiro',
        'account' => [
            'number' => '12345-X',
            'type' => 'checking'
        ]
    ]
);
```

---

# 📤 Serializing Objects

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

# 📚 Generic Types (list<T>, map<K,V>, shapes)

## List Example

```php
$list = $mapper->readValue('list<LatLng>', $json);
```

Using `generic()`:

```php
$list = $mapper->readValue(
    generic('list', LatLng::class),
    $json
);
```

## Map Example

```php
$type = generic('map', ['string', Address::class]);

$result = $mapper->readValue($type, [
    'main'  => Address::mainData(),
    'other' => Address::otherData(),
]);
```

## Array Shape Example

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

## Object Shape Example

```php
$type = shape('object', [
    'type' => AccountType::class,
    'position' => LatLng::class
]);
```

Produces a `stdClass`.

---

# 🧩 Custom Type Mappers

From the test suite:

```php
$mapper = new ArrayObjectMapper(
    typeMappers: [
        LatLng::class => [
            'reader' => fn (string $value) => new LatLng(...explode(',', $value))
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

---

# 🕒 Date Handling

PHP‑Jackson provides built‑in support for:

- DateTime
- DateTimeImmutable
- Carbon
- CarbonImmutable
- DateTimeInterface

All are parsed via ISO 8601 strings:

```php
[
  'datetime' => '2025-10-22T11:21:31+00:00'
]
```

---

# ❗ Error Handling

When parsing fails, the library throws:

### `UnableToParseValue`

With fields:

```php
$e->trace;          // ['address','place','position']
$e->expected;       // expected type description
$e->given;          // actual given value
```

Message example:

```
Unable to parse value at .address.place.position
```

This makes debugging extremely easy.

---

# 🧪 Examples Extracted from Tests

All usage patterns shown above are taken directly from:

```
tests/
  JsonObjectMapperTest.php
  ArrayObjectMapperTest.php
  ReadmeTest.php
  DateTimeReaderTest.php
---
```


# ✅ Summary

You can:

- read JSON → typed objects via `JsonObjectMapper`
- read arrays → typed objects via `ArrayObjectMapper`
- merge missing fields using `readValueWith`
- write objects → JSON/arrays via `writeValue`
- use generics (`list<T>`, `map<K,V>`, shapes)
- plug custom mappers for any class
- rely on strong error tracing

This file is ready to be added to:

```
docs/index.md
```

or any GH Pages folder.

---
