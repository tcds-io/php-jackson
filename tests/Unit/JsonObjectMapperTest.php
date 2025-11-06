<?php

namespace Test\Tcds\Io\Jackson\Unit;

use PHPUnit\Framework\Attributes\Test;
use Test\Tcds\Io\Jackson\Fixture\ReadOnly\AccountHolder;
use Tcds\Io\Jackson\JsonObjectMapper;
use Test\Tcds\Io\Jackson\SerializerTestCase;

class JsonObjectMapperTest extends SerializerTestCase
{
    private object $object;
    private string $json;
    private string $partialJson;

    private JsonObjectMapper $mapper;

    protected function setUp(): void
    {
        $this->object = AccountHolder::thiagoCordeiro();
        $this->json = AccountHolder::json();
        $this->partialJson = AccountHolder::partialJsonValue();

        $this->mapper = new JsonObjectMapper();
    }

    #[Test] public function given_a_json_then_read_value_into_the_object(): void
    {
        $accountHolder = $this->mapper->readValueWith(AccountHolder::class, $this->json, []);

        $this->assertEquals($this->object, $accountHolder);
    }

    #[Test] public function given_a_json_then_parse_into_the_object_with_additional_content(): void
    {
        $accountHolder = $this->mapper->readValueWith(AccountHolder::class, $this->partialJson, [
            'name' => 'Thiago Cordeiro',
            'account' => [
                'number' => '12345-X',
                'type' => 'checking',
            ],
        ]);

        $this->assertEquals($this->object, $accountHolder);
    }
}
