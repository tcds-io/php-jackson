<?php

declare(strict_types=1);

namespace Test\Tcds\Io\Jackson\Unit\Node;

use PHPUnit\Framework\Attributes\Test;
use Tcds\Io\Jackson\ArrayObjectMapper;
use Test\Tcds\Io\Jackson\Fixture\Money;
use Test\Tcds\Io\Jackson\Fixture\Slug;
use Test\Tcds\Io\Jackson\SerializerTestCase;

class JsonMapperTest extends SerializerTestCase
{
    #[Test]
    public function read_uses_reader_from_class_attribute(): void
    {
        $this->assertEquals(new Money(1050), $this->arrayMapper->readValue(Money::class, 1050));
        $this->assertEquals(new Money(1050), $this->arrayMapper->readValue(Money::class, '$10.50'));
    }

    #[Test]
    public function write_uses_writer_from_class_attribute(): void
    {
        $this->assertSame('$10.50', $this->arrayMapper->writeValue(new Money(1050)));
        $this->assertSame('"$10.50"', $this->jsonMapper->writeValue(new Money(1050)));
    }

    #[Test]
    public function explicit_type_mappers_override_class_attribute(): void
    {
        // explicit closure on the constructor argument wins over the #[JsonMapper] attribute
        $mapper = new ArrayObjectMapper(typeMappers: [
            Money::class => [
                'reader' => fn (mixed $data) => new Money(((int) ($data ?? 0)) * 2),
                'writer' => fn (Money $data) => $data->cents,
            ],
        ]);

        $this->assertEquals(new Money(20), $mapper->readValue(Money::class, 10));
        $this->assertSame(20, $mapper->writeValue(new Money(20)));
    }

    #[Test]
    public function read_uses_invokable_class_from_class_attribute(): void
    {
        // SlugReader has __invoke but does not implement Reader — should still work
        $this->assertEquals(new Slug('hello-world'), $this->arrayMapper->readValue(Slug::class, 'Hello World!'));
    }

    #[Test]
    public function write_uses_invokable_class_from_class_attribute(): void
    {
        // SlugWriter has __invoke but does not implement Writer — should still work
        $this->assertSame('hello-world', $this->arrayMapper->writeValue(new Slug('hello-world')));
    }

    #[Test]
    public function json_mapper_accepts_closure_when_constructed_programmatically(): void
    {
        // PHP attributes can't carry literal closures, but JsonMapper itself
        // accepts a MapperClosure when built directly (e.g. for tests or
        // dynamic registration). The resolver short-circuits on Closure.
        $jsonMapper = new \Tcds\Io\Jackson\Node\JsonMapper(
            reader: fn (mixed $data) => new Money(((int) ($data ?? 0)) + 1),
            writer: fn (Money $data) => $data->cents - 1,
        );

        $this->assertSame(11, ($jsonMapper->writer)(new Money(12), Money::class, $this->arrayMapper, []));
        $this->assertEquals(new Money(13), ($jsonMapper->reader)(12, Money::class, $this->arrayMapper, []));
    }
}
