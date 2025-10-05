<?php

declare(strict_types=1);

namespace Tcds\Io\Serializer\Fixture\ReadOnly;

use Tcds\Io\Serializer\Metadata\InputNode;
use Tcds\Io\Serializer\Metadata\TypeNode;

readonly class Address
{
    public function __construct(
        public string $street,
        public int $number,
        public bool $main,
        public Place $place,
    ) {
    }

    public static function main(): self
    {
        return new self(
            street: 'main street',
            number: 150,
            main: true,
            place: new Place(
                city: 'Santa Catarina',
                country: 'Brazil',
                position: new LatLng(
                    lat: -26.9013,
                    lng: -48.6655,
                ),
            ),
        );
    }

    public static function other(): self
    {
        return new self(
            street: 'street street',
            number: 100,
            main: false,
            place: new Place(
                city: 'São Paulo',
                country: 'Brazil',
                position: new LatLng(
                    lat: -26.9013,
                    lng: -48.6655,
                ),
            ),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function mainData(): array
    {
        return [
            'street' => 'main street',
            'number' => '150',
            'main' => 'true',
            'place' => [
                'city' => 'Santa Catarina',
                'country' => 'Brazil',
                'position' => [
                    'lat' => '-26.9013',
                    'lng' => '-48.6655',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function otherData(): array
    {
        return [
            'street' => 'street street',
            'number' => '100',
            'main' => 'false',
            'place' => [
                'city' => 'São Paulo',
                'country' => 'Brazil',
                'position' => [
                    'lat' => '-26.9013',
                    'lng' => '-48.6655',
                ],
            ],
        ];
    }

    public static function node(): TypeNode
    {
        return new TypeNode(
            type: Address::class,
            inputs: [
                'street' => new InputNode('street', new TypeNode(type: 'string')),
                'number' => new InputNode('number', new TypeNode(type: 'int')),
                'main' => new InputNode('main', new TypeNode(type: 'bool')),
                'place' => new InputNode('place', Place::node()),
            ],
        );
    }

    public static function fingerprint(): string
    {
        return sprintf('%s[%s, %s, %s, %s]', self::class, 'string', 'int', 'bool', Place::fingerprint());
    }
}
