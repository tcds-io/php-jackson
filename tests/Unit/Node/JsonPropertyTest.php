<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Jackson\Unit\Node;

use PHPUnit\Framework\Attributes\Test;
use Tcds\Io\Jackson\Exception\UnableToParseValue;
use Tcds\Io\Jackson\Node\Runtime\RuntimeTypeNodeSpecificationFactory;
use Test\Tcds\Io\Jackson\Fixture\ReadOnly\SnakeCaseDto;
use Test\Tcds\Io\Jackson\SerializerTestCase;

class JsonPropertyTest extends SerializerTestCase
{
    private SnakeCaseDto $object {
        get => new SnakeCaseDto(firstName: 'Arthur', lastName: 'Dent', age: 42);
        set => $this->object = $value;
    }

    private array $array = [
        'first_name' => 'Arthur',
        'last_name' => 'Dent',
        'age' => 42,
    ];

    private string $json = <<<JSON
    {
        "first_name": "Arthur",
        "last_name": "Dent",
        "age": 42
    }
    JSON;

    #[Test]
    public function read_array_with_renamed_keys(): void
    {
        $this->assertEquals($this->object, $this->arrayMapper->readValue(SnakeCaseDto::class, $this->array));
    }

    #[Test]
    public function read_json_with_renamed_keys(): void
    {
        $this->assertEquals($this->object, $this->jsonMapper->readValue(SnakeCaseDto::class, $this->json));
    }

    #[Test]
    public function write_object_uses_renamed_keys(): void
    {
        $this->assertEquals($this->array, $this->arrayMapper->writeValue($this->object));
        $this->assertJsonStringEqualsJsonString($this->json, $this->jsonMapper->writeValue($this->object));
    }

    #[Test]
    public function error_path_uses_wire_key(): void
    {
        $partial = ['last_name' => 'Dent', 'age' => 42];

        /** @var UnableToParseValue $exception */
        $exception = $this->expectThrows(
            fn () => $this->arrayMapper->readValue(SnakeCaseDto::class, $partial),
        );

        $this->assertInstanceOf(UnableToParseValue::class, $exception);
        $this->assertContains('first_name', $exception->path);
    }

    #[Test]
    public function specification_uses_wire_key(): void
    {
        $spec = new RuntimeTypeNodeSpecificationFactory()->create(SnakeCaseDto::class);

        $this->assertEquals(
            ['first_name' => 'string', 'last_name' => 'string', 'age' => 'int'],
            $spec,
        );
    }
}
