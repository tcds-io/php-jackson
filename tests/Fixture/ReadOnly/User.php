<?php

declare(strict_types=1);

namespace Tcds\Io\Serializer\Fixture\ReadOnly;

use Tcds\Io\Serializer\Metadata\Node\ReadNode;
use Tcds\Io\Serializer\Metadata\TypeNode;

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

    public static function node(): TypeNode
    {
        return new TypeNode(
            type: User::class,
            inputs: [
                'name' => new ReadNode('name', new TypeNode('string')),
                'age' => new ReadNode('age', new TypeNode('int')),
                'height' => new ReadNode('height', new TypeNode('float')),
                'address' => new ReadNode('address', Address::node()),
            ],
        );
    }

    public static function fingerprint(): string
    {
        return sprintf('%s[%s, %s, %s, %s]', self::class, 'string', 'int', 'float', Address::fingerprint());
    }
}
