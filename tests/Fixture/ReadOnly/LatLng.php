<?php

declare(strict_types=1);

namespace Tcds\Io\Serializer\Fixture\ReadOnly;

use Tcds\Io\Serializer\Metadata\ParamNode;
use Tcds\Io\Serializer\Metadata\TypeNode;

readonly class LatLng
{
    public function __construct(
        public float $lat,
        public float $lng,
    ) {
    }

    public static function node(): TypeNode
    {
        return new TypeNode(
            type: LatLng::class,
            params: [
                'lat' => new ParamNode(new TypeNode('float')),
                'lng' => new ParamNode(new TypeNode('float')),
            ],
        );
    }

    public static function fingerprint(): string
    {
        return sprintf('%s[%s, %s]', self::class, 'float', 'float');
    }
}
