<?php

declare(strict_types=1);

namespace Tcds\Io\Serializer\Unit\Metadata;

use PHPUnit\Framework\Attributes\Test;
use Tcds\Io\Generic\ArrayList;
use Tcds\Io\Serializer\Fixture\Pair;
use Tcds\Io\Serializer\Fixture\ReadOnly\Address;
use Tcds\Io\Serializer\Fixture\ReadOnly\LatLng;
use Tcds\Io\Serializer\Fixture\WithShape;
use Tcds\Io\Serializer\Metadata\Node\ReadNode;
use Tcds\Io\Serializer\Metadata\TypeNode;
use Tcds\Io\Serializer\SerializerTestCase;

class InputNodeTest extends SerializerTestCase
{
    #[Test] public function data_class_address(): void
    {
        $type = Address::class;

        $inputs = ReadNode::of($type);
        $this->initializeReadNodes($inputs);

        $this->assertEquals(
            expected: Address::node()->inputs,
            actual: $inputs,
        );
    }

    #[Test] public function generic_class_pair(): void
    {
        $type = generic(Pair::class, ['string', 'float']);

        $nodes = ReadNode::of($type);
        $this->initializeReadNodes($nodes);

        $this->assertEquals(
            expected: [
                'key' => new ReadNode('key', new TypeNode('string')),
                'value' => new ReadNode('value', new TypeNode('float')),
            ],
            actual: $nodes,
        );
    }

    #[Test] public function generic_class_array_list(): void
    {
        $type = generic(ArrayList::class, [LatLng::class]);

        $nodes = ReadNode::of($type);
        $this->initializeReadNodes($nodes);

        $this->assertEquals(
            expected: [
                'items' => new ReadNode(
                    name: 'items',
                    node: new TypeNode(
                        type: generic('list', [LatLng::class]),
                        inputs: [
                            'value' => new ReadNode(name: 'value', node: Latlng::node()),
                        ],
                        outputs: [],
                    ),
                ),
            ],
            actual: $nodes,
        );
    }

    #[Test] public function shaped_class(): void
    {
        $type = WithShape::class;

        $nodes = ReadNode::of($type);
        $this->initializeReadNodes($nodes);

        $this->assertEquals(
            expected: WithShape::node()->inputs,
            actual: $nodes,
        );
    }
}
