<?php

declare(strict_types=1);

namespace Tcds\Io\Serializer\Fixture\ReadOnly;

use Tcds\Io\Serializer\Metadata\ParamNode;
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

    public static function node(): TypeNode
    {
        return new TypeNode(
            type: User::class,
            params: [
                'name' => new ParamNode(new TypeNode('string')),
                'age' => new ParamNode(new TypeNode('int')),
                'height' => new ParamNode(new TypeNode('float')),
                'address' => new ParamNode(Address::node()),
            ],
        );
    }

    public static function fingerprint(): string
    {
        return sprintf('%s[%s, %s, %s, %s]', self::class, 'string', 'int', 'float', Address::fingerprint());
    }
}
