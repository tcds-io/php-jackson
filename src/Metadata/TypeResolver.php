<?php

namespace Tcds\Io\Serializer\Metadata;

use Tcds\Io\Serializer\Exception\SerializerException;
use Tcds\Io\Serializer\Metadata\Parser\Annotation;
use Tcds\Io\Serializer\Metadata\Parser\ClassAnnotation;

class TypeResolver
{
    /**
     * @param string $type
     * @return array{ 0: class-string<mixed>, 1: list<mixed> }
     */
    public static function from(string $type): array
    {
        [$type, $generics] = Annotation::extractGenerics($type);
        $templates = ClassAnnotation::templates(class: $type);

        foreach (array_keys($templates) as $position => $template) {
            $templates[$template] = $generics[$position] ?? throw new SerializerException("No generic defined for template `$template`");
        }

        return [$type, $templates];
    }
}
