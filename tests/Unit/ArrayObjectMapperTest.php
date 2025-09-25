<?php

namespace Tcds\Io\Serializer\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tcds\Io\Serializer\ArrayObjectMapper;
use Tcds\Io\Serializer\Fixture\ReadOnly\AccountHolder;
use Tcds\Io\Serializer\Mapper\Reader;
use Tcds\Io\Serializer\Runtime\RuntimeReader;
use Tcds\Io\Serializer\SerializerTestCase;

class ArrayObjectMapperTest extends SerializerTestCase
{
    private object $object;
    /** @var array<string, mixed> */
    private array $data;
    /** @var array<string, mixed> */
    private array $partialData;

    private Reader $reader;

    protected function setUp(): void
    {
        $this->object = AccountHolder::thiagoCordeiro();
        $this->data = json_decode(AccountHolder::json(), true);
        $this->partialData = json_decode(AccountHolder::partialJsonValue(), true);

        $this->reader = new RuntimeReader();
    }

    #[Test] public function given_a_json_then_read_value_into_the_object(): void
    {
        $mapper = new ArrayObjectMapper($this->reader, []);

        $accountHolder = $mapper->readValueWith(AccountHolder::class, $this->data, []);

        $this->assertEquals($this->object, $accountHolder);
    }

    #[Test] public function given_a_json_then_parse_into_the_object_with_additional_content(): void
    {
        $mapper = new ArrayObjectMapper($this->reader, []);

        $accountHolder = $mapper->readValueWith(AccountHolder::class, $this->partialData, [
            'name' => 'Thiago Cordeiro',
            'account' => [
                'number' => '12345-X',
                'type' => 'checking',
            ],
        ]);

        $this->assertEquals($this->object, $accountHolder);
    }
}
