<?php

namespace Tcds\Io\Serializer\Metadata\Parser;

use ReflectionClass;
use ReflectionParameter;
use Tcds\Io\Generic\ArrayList;
use Tcds\Io\Serializer\Metadata\Reflection;

class ClassParams
{
    /**
     * @template T
     * @param class-string<T>|null $class
     * @param ReflectionClass<T>|null $reflection
     * @return array<string, string>
     */
    public static function of(?ReflectionClass $reflection = null, ?string $class = null): array
    {
        return new ArrayList(Reflection::of($reflection, $class)
            ->getConstructor()
            ->getParameters())
            ->indexedBy(fn(ReflectionParameter $param) => $param->name)
            ->mapValues(fn(ReflectionParameter $param) => ParamType::of($param))
            ->entries();
    }
}
