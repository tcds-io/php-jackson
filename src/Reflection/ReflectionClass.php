<?php

namespace Tcds\Io\Serializer\Reflection;

use Override;
use ReflectionClass as OriginalReflectionClass;
use ReflectionMethod as OriginalReflectionMethod;
use ReflectionProperty as OriginalReflectionProperty;
use Tcds\Io\Serializer\Metadata\Parser\Annotation;
use Tcds\Io\Serializer\Metadata\Parser\ClassAnnotation;

/**
 * @template T
 */
class ReflectionClass extends OriginalReflectionClass
{
    /** @var list<string> */
    public array $generics;

    /** @var list<class-string<mixed>> */
    public array $templates;

    /** @var array<string, class-string<mixed>> */
    public array $aliases;

    public function __construct(string $type)
    {
        [$class, $generics] = Annotation::extractGenerics($type);

        parent::__construct($class);

        $this->generics = $generics;
        $this->templates = ClassAnnotation::templates(reflection: $this, generics: $generics);
        $this->aliases = ClassAnnotation::aliases(reflection: $this);
    }

    #[Override] public function getMethod(string $name): OriginalReflectionMethod
    {
        return new ReflectionMethod($this, $name);
    }

    #[Override] public function getConstructor(): ?OriginalReflectionMethod
    {
        return $this->getMethod('__construct');
    }

    #[Override] public function getProperty(string $name): ReflectionProperty
    {
        return new ReflectionProperty($this, $name);
    }

    #[Override] public function getProperties($filter = null): array
    {
        return array_map(
            fn(OriginalReflectionProperty $prop) => $this->getProperty($prop->name),
            parent::getProperties(),
        );
    }
}
