<?php

declare(strict_types=1);

namespace Tcds\Io\Serializer\Fixture\ReadOnly;

use Tcds\Io\Serializer\Metadata\ParamNode;
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
            params: [
                'city' => new ParamNode(new TypeNode('string')),
                'country' => new ParamNode(new TypeNode('string')),
                'position' => new ParamNode(LatLng::node()),
            ],
        );
    }

    public static function fingerprint(): string
    {
        return sprintf('%s[%s, %s, %s]', self::class, 'string', 'string', LatLng::fingerprint());
    }
}
