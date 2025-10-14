<?php

namespace Tcds\Io\Serializer\Reflection;

use ReflectionClass;
use ReflectionParameter;
use Tcds\Io\Generic\ArrayList;
use Tcds\Io\Serializer\Metadata\Node\ReadNode;
use Tcds\Io\Serializer\Metadata\Parser\Annotation;
use Tcds\Io\Serializer\Metadata\Parser\ClassAnnotation;
use Tcds\Io\Serializer\Metadata\Parser\Type;

class SerializerReflection
{
    private string $class;
    private ReflectionClass $reflection;

    /** @var list<class-string<mixed>> */
    private array $templates;

    /** @var array<string, class-string<mixed>> */
    private array $aliases;

    public function __construct(string $type)
    {
        [$class, $generics] = Annotation::extractGenerics($type);

        $this->class = $class;
        $this->reflection = new ReflectionClass($class);
        $this->templates = ClassAnnotation::templates(reflection: $this->reflection, generics: $generics);
        $this->aliases = ClassAnnotation::aliases(reflection: $this->reflection);
    }

    /**
     * @return array
     */
    public function getInputs(): array
    {
        $params = $this->reflection->getConstructor()->getParameters();

        return new ArrayList($params)
            ->map(function (ReflectionParameter $param) {
                [$type, $generics] = $this->typeOf($param);

                return new ReadNode($param->name, generic($type, $generics));
            })
            ->items();
    }

    /**
     * @param ReflectionParameter $param
     * @return array{ 0: string, 1: list<string> }
     */
    private function typeOf(ReflectionParameter $param): array
    {
        $type = Annotation::param(
            function: $param->getDeclaringFunction(),
            name: $param->name,
        ) ?: $param->getType()->getName();

        if (Type::isResolvedType($type)) {
            return [$type, []];
        }

        if (Type::isShapeType($type)) {
            [$type, $params] = Annotation::shapedFqn($this->reflection, $type);
            $type = sprintf('%s%s', $type, json_encode($params));

            return [$type, []];
        }

        $type = $this->aliases[$type] ?? $type;
        [$type, $generics] = Annotation::extractGenerics($type);
        $type = $this->templates[$type] ?? $type;

        foreach ($generics as $index => $generic) {
            $generics[$index] = $this->templates[$index] ?? $this->templates[$generic] ?? $generic;
        }

        return [$type, $generics];
    }
}
