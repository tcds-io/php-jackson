<?php

namespace Tcds\Io\Serializer\Unit\Metadata\Parser;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tcds\Io\Serializer\Fixture\AccountType;
use Tcds\Io\Serializer\Fixture\ReadOnly\Address;
use Tcds\Io\Serializer\Metadata\Parser\Type;

class TypeTest extends TestCase
{
    #[DataProvider('typeDataset')]
    #[Test] public function value_type(mixed $value, string $type): void
    {
        $this->assertEquals($type, Type::ofValue($value));
    }

    /**
     * @return array<string, array{value: mixed, type: string}>
     */
    public static function typeDataset(): array
    {
        return [
            'string' => [
                'value' => 'foo',
                'type' => 'string',
            ],
            'float' => [
                'value' => 19.90,
                'type' => 'float',
            ],
            'boolean' => [
                'value' => true,
                'type' => 'boolean',
            ],
            'enum' => [
                'value' => AccountType::CHECKING,
                'type' => AccountType::class,
            ],
            'class' => [
                'value' => Address::main(),
                'type' => Address::class,
            ],
            'list' => [
                'value' => ['foo', 'bar'],
                'type' => 'list<string>',
            ],
            'map<string, string>' => [
                'value' => ['foo' => 'bar'],
                'type' => 'map<string, string>',
            ],
            'map<string, boolean>' => [
                'value' => ['email' => true],
                'type' => 'map<string, boolean>',
            ],
            'map<int, string>' => [
                'value' => [10 => 'Foo'],
                'type' => 'map<integer, string>',
            ],
            'map<string, Address>' => [
                'value' => ['email' => Address::main()],
                'type' => generic('map', ['string', Address::class]),
            ],
        ];
    }
}
