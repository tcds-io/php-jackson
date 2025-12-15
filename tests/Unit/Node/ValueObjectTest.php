<?php

namespace Test\Tcds\Io\Jackson\Unit\Node;

use PHPUnit\Framework\Attributes\Test;
use Test\Tcds\Io\Jackson\Fixture\ReadOnly\Contact;
use Test\Tcds\Io\Jackson\Fixture\ReadOnly\Email;
use Test\Tcds\Io\Jackson\SerializerTestCase;

class ValueObjectTest extends SerializerTestCase
{
    private Contact $object {
        get => new Contact(new Email('foo@bar.com'));
        set => $this->object = $value;
    }

    private string $json = <<<JSON
        {
            "email": "foo@bar.com"
        }
        JSON;

    private array $array = [
        'email' => 'foo@bar.com',
    ];

    #[Test]
    public function write_value_object(): void
    {
        $this->assertEquals($this->array, $this->arrayMapper->writeValue($this->object));
        $this->assertJsonStringEqualsJsonString($this->json, $this->jsonMapper->writeValue($this->object));
    }

    #[Test]
    public function read_value_object(): void
    {
        $this->assertEquals($this->object, $this->jsonMapper->readValue(Contact::class, $this->json));
        $this->assertEquals($this->object, $this->arrayMapper->readValue(Contact::class, $this->array));
    }
}
