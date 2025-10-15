<?php

declare(strict_types=1);

namespace Tcds\Io\Serializer\Reflection\Type;

use Tcds\Io\Serializer\Metadata\Parser\Annotation;
use Tcds\Io\Serializer\Reflection\ReflectionClass;

class GenericReflectionType extends ReflectionType
{
    /**
     * @param ReflectionClass $reflection
     * @param string $type
     * @param list<string> $generics
     */
    public function __construct(ReflectionClass $reflection, string $type, public readonly array $generics)
    {
        parent::__construct($reflection, $type);
    }

    public static function from(ReflectionClass $reflection, string $type): self
    {
        [$type, $generics] = Annotation::extractGenerics($type);
        $type = $reflection->templates[$type] ?? $type;

        foreach ($generics as $index => $generic) {
            $genericType = $reflection->templates[$index] ?? $reflection->templates[$generic] ?? $generic;

            $generics[$index] = Annotation::fqnOf($reflection, $genericType);
        }

        return new self($reflection, $type, $generics);
    }

    public function getName(): string
    {
        return generic($this->type, $this->generics);
    }
}
