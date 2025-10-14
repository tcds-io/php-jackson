<?php

namespace Tcds\Io\Serializer\Metadata\Parser;

use ReflectionClass;
use Tcds\Io\Serializer\Exception\SerializerException;
use Tcds\Io\Serializer\Metadata\Reflection;

class ClassAnnotation
{
    /**
     * @template T
     * @param null|ReflectionClass<T> $reflection
     * @param null|class-string<T> $class
     * @param list<string> $generics
     * @return array<string, class-string<mixed>>
     */
    public static function templates(
        ?ReflectionClass $reflection = null,
        ?string $class = null,
        array $generics = [],
    ): array {
        $reflection = Reflection::of($reflection, $class);

        $docblock = $reflection->getDocComment() ?? '';
        preg_match_all('/@template\s+(\w+)(?:\s+of\s+(\w+))?/', $docblock, $matches);
        $indexes = array_keys($matches[1] ?? []);

        $templates = [];

        foreach ($indexes as $index) {
            $key = $matches[1][$index];
            $value = $matches[2][$index];

            $templates[$key] = $value ?: 'mixed';
        }

        foreach (array_keys($templates) as $position => $template) {
            $templates[$template] = $generics[$position] ?? throw new SerializerException("No generic defined for template `$template`");
        }

        return $templates;
    }

    /**
     * @template T
     * @param ReflectionClass<T> $reflection
     * @return array<string, class-string<mixed>>
     */
    public static function aliases(ReflectionClass $reflection): array
    {
        $docblock = $reflection->getDocComment() ?? '';
        preg_match_all('/@phpstan-type\s+(\w+)\s+(.*)?/', $docblock, $matches);
        $indexes = array_keys($matches[1] ?? []);

        $types = [];

        foreach ($indexes as $index) {
            $name = $matches[1][$index];
            $type = $matches[2][$index];

            $types[$name] = $type;
        }

        return $types;
    }

    /**
     * @template T
     * @param class-string<T>|null $class
     * @param ReflectionClass<T>|null $reflection
     * @return array<string, class-string<mixed>>
     */
    public static function params(?ReflectionClass $reflection = null, ?string $class = null): array
    {
        $reflection = Reflection::of($reflection, $class);

        $docblock = $reflection->getConstructor()?->getDocComment() ?? '';
        preg_match_all('/@param\s+(\w+)\s+\$(.*)?/', $docblock, $matches);
        $indexes = array_keys($matches[1] ?? []);
        $types = self::aliases($reflection);

        $params = [];

        foreach ($indexes as $index) {
            $type = $matches[1][$index];
            $name = $matches[2][$index];

            $params[$name] = $types[$type] ?? $type;
        }

        return $params;
    }
}
