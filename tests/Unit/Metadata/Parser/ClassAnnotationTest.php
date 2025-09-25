<?php

namespace Tcds\Io\Serializer\Unit\Metadata\Parser;

use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use Tcds\Io\Generic\ArrayList;
use Tcds\Io\Serializer\Fixture\Pair;
use Tcds\Io\Serializer\Fixture\ReadOnly\Address;
use Tcds\Io\Serializer\Metadata\Parser\ClassAnnotation;
use Tcds\Io\Serializer\SerializerTestCase;

class ClassAnnotationTest extends SerializerTestCase
{
    #[Test] public function params_of_pair(): void
    {
        $reflection = new ReflectionClass(Pair::class);

        $params = ClassAnnotation::params($reflection);

        $this->assertEquals(
            expected: [
                'key' => 'K',
                'value' => 'V',
            ],
            actual: $params,
        );
    }

    #[Test] public function params_of_array_list(): void
    {
        $reflection = new ReflectionClass(ArrayList::class);

        $params = ClassAnnotation::params($reflection);

        $this->assertEquals(
            expected: [
                'items' => 'list<GenericItem>'
            ],
            actual: $params,
        );
    }

    #[Test] public function templates_of_pair(): void
    {
        $reflection = new ReflectionClass(Pair::class);

        $templates = ClassAnnotation::templates($reflection);

        $this->assertEquals(
            expected: [
                'K' => 'mixed',
                'V' => 'object',
            ],
            actual: $templates,
        );
    }

    #[Test] public function templates_of_array_list(): void
    {
        $reflection = new ReflectionClass(ArrayList::class);

        $templates = ClassAnnotation::templates($reflection);

        $this->assertEquals(
            expected: [
                'GenericItem' => 'mixed',
            ],
            actual: $templates,
        );
    }

    #[Test] public function templates_of_address(): void
    {
        $reflection = new ReflectionClass(Address::class);

        $templates = ClassAnnotation::templates($reflection);

        $this->assertEquals(
            expected: [],
            actual: $templates,
        );
    }

    #[Test] public function runtime_types_of_array_list(): void
    {
        $reflection = new ReflectionClass(ArrayList::class);

        $templates = ClassAnnotation::runtimeTypes($reflection);

        $this->assertEquals(
            expected: [
                'Primitive' => 'string|int|float|bool',
                'Entries' => 'list<GenericItem>',
            ],
            actual: $templates,
        );
    }

    #[Test] public function runtime_types_of_address(): void
    {
        $reflection = new ReflectionClass(Address::class);

        $templates = ClassAnnotation::runtimeTypes($reflection);

        $this->assertEquals(expected: [], actual: $templates);
    }
}
