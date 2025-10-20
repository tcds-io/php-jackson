<?php

declare(strict_types=1);

namespace Tcds\Io\Serializer\Fixture\ReadOnly;

readonly class User
{
    public function __construct(
        public string $name,
        public int $age,
        public float $height = 1.50,
        public ?Address $address = null,
    ) {
    }

    public static function arthurDent(): self
    {
        return new self(
            name: 'Arthur Dent',
            age: 27,
            height: 1.77,
            address: Address::main(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function arthurDentData(): array
    {
        return [
            'name' => 'Arthur Dent',
            'age' => 27,
            'height' => 1.77,
            'address' => Address::mainData(),
        ];
    }
}
