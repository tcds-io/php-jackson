<?php

namespace Tcds\Io\Serializer\Unit\Metadata\Parser;

use Exception;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Tcds\Io\Serializer\Fixture\Pair;
use Tcds\Io\Serializer\Fixture\ReadOnly\Address;
use Tcds\Io\Serializer\Metadata\Parser\Annotation;

class AnnotationTest extends TestCase
{
    #[Test] public function given_an_annotated_generic_type_then_get_generics(): void
    {
        $annotation = sprintf('%s<%s, %s>', Pair::class, 'string', Address::class);

        [$type, $generics] = Annotation::extractGenerics($annotation);

        $this->assertEquals(Pair::class, $type);
        $this->assertEquals(['string', Address::class], $generics);
    }

    #[Test] public function get_param_annotation(): void
    {
        $function = new ReflectionClass(Pair::class)->getConstructor() ?: throw new Exception('Class does not have constructor');

        $foo = Annotation::param($function, name: 'foo');
        $key = Annotation::param($function, name: 'key');
        $value = Annotation::param($function, name: 'value');

        $this->assertEquals(null, $foo);
        $this->assertEquals('K', $key);
        $this->assertEquals('V', $value);
    }
}
