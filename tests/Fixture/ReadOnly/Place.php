<?php

declare(strict_types=1);

namespace Tcds\Io\Serializer\Fixture\ReadOnly;

use Tcds\Io\Serializer\Metadata\InputNode;
use Tcds\Io\Serializer\Metadata\TypeNode;

readonly class Place
{
    public function __construct(
        public string $city,
        public string $country,
        public LatLng $position,
    ) {
    }

    public static function node(): TypeNode
    {
        return new TypeNode(
            type: Place::class,
            inputs: [
                'city' => new InputNode('city', new TypeNode('string')),
                'country' => new InputNode('country', new TypeNode('string')),
                'position' => new InputNode('position', LatLng::node()),
            ],
        );
    }

    public static function fingerprint(): string
    {
        return sprintf('%s[%s, %s, %s]', self::class, 'string', 'string', LatLng::fingerprint());
    }
}
