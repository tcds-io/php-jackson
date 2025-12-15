<?php

namespace Test\Tcds\Io\Jackson\Fixture;

use Test\Tcds\Io\Jackson\Fixture\ReadOnly\LatLng;
use Tcds\Io\Jackson\Node\InputNode;
use Tcds\Io\Jackson\Node\OutputNode;
use Tcds\Io\Jackson\Node\TypeNode;

/**
 * @template K
 * @template V of object
 */
readonly class Pair
{
    /**
     * @param K $key
     * @param V $value
     */
    public function __construct(public mixed $key, public object $value) {}

    /**
     * @return K
     */
    public function key(): mixed
    {
        return $this->key;
    }

    /**
     * @return V
     */
    public function value(): object
    {
        return $this->value;
    }

    public static function node(): TypeNode
    {
        return new TypeNode(
            type: generic(Pair::class, ['string', LatLng::class]),
            inputs: [
                new InputNode(name: 'key', type: 'string', default: null),
                new InputNode(name: 'value', type: LatLng::class, default: null),
            ],
            outputs: [
                OutputNode::property(name: 'key', type: 'string'),
                OutputNode::property(name: 'value', type: LatLng::class),
            ],
        );
    }
}
