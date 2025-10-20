<?php

namespace Tcds\Io\Serializer\Unit\Metadata\Parser;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Tcds\Io\Serializer\Fixture\Pair;
use Tcds\Io\Serializer\Fixture\ReadOnly\Address;
use Tcds\Io\Serializer\Fixture\ReadOnly\Place;
use Tcds\Io\Serializer\Metadata\Parser\Type;

class ParamTypeTest extends TestCase
{
    #[Test] public function given_a_param_of_generic_class_then_get_its_generic_type(): void
    {
        $class = Pair::class;

        [$key, $value] = new ReflectionClass($class)
            ->getConstructor()
            ->getParameters();

        $this->assertEquals('K', Type::ofParam($key));
        $this->assertEquals('V', Type::ofParam($value));
    }

    #[Test] public function given_a_param_of_a_class_then_get_its_type(): void
    {
        $class = Address::class;

        [$street, $number, $main, $place] = new ReflectionClass($class)
            ->getConstructor()
            ->getParameters();

        $this->assertEquals('string', Type::ofParam($street));
        $this->assertEquals('int', Type::ofParam($number));
        $this->assertEquals('bool', Type::ofParam($main));
        $this->assertEquals(Place::class, Type::ofParam($place));
    }
}
