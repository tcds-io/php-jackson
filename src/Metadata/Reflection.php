<?php

namespace Tcds\Io\Serializer\Metadata;

use ReflectionClass;
use Tcds\Io\Serializer\Exception\SerializerException;

readonly class Reflection
{
    public static function of(?ReflectionClass $reflection = null, ?string $class = null)
    {
        return match (true) {
            !is_null($reflection) => $reflection,
            !is_null($class) => new ReflectionClass($class),
            default => throw new SerializerException('Either $class or $reflection must be provided'),
        };
    }
}
