<?php

namespace Tcds\Io\Serializer\Metadata\Node;

use ReflectionParameter;
use Tcds\Io\Generic\ArrayList;
use Tcds\Io\Serializer\Exception\SerializerException;
use Tcds\Io\Serializer\Metadata\Parser\Annotation;
use Tcds\Io\Serializer\Metadata\Parser\ClassAnnotation;
use Tcds\Io\Serializer\Metadata\Parser\Type;
use Tcds\Io\Serializer\Metadata\Reflection;
use Tcds\Io\Serializer\Metadata\TypeNode;

readonly class ReadNode
{
    public function __construct(
        public string $name,
        public TypeNode $node,
    ) {
    }

    public function __toString(): string
    {
        return "$this->node";
    }

    public static function from(string $name, string $type): self
    {
        return lazyOf(self::class, fn() => new self($name, TypeNode::from($type)));
    }

    /**
     * @template T
     * @param class-string<T> $type
     * @return list<ReadNode>
     */
    public static function of(string $type): array
    {
        [$type, $generics] = Annotation::extractGenerics($type);
        $reflection = Reflection::of(class: $type);
        $templates = ClassAnnotation::templates(reflection: $reflection);

        foreach (array_keys($templates) as $position => $template) {
            $templates[$template] = $generics[$position] ?? throw new SerializerException("No generic defined for template `$template`");
        }

        return new ArrayList($reflection
            ->getConstructor()
            ->getParameters())
            ->map(function (ReflectionParameter $param) use ($templates) {
                $paramType = Type::ofParam($param);
                $paramType = $templates[$paramType] ?? $paramType;
                [$paramType, $paramGenerics] = Annotation::extractGenerics($paramType);

                foreach ($paramGenerics as $index => $paramGeneric) {
                    $paramGenerics[$index] = $templates[$index] ?? $templates[$paramGeneric] ?? $paramGeneric;
                }

                return ReadNode::from($param->name, generic($paramType, $paramGenerics));
            })
            ->indexedBy(fn(ReadNode $node) => $node->name)
            ->entries();
    }
}
