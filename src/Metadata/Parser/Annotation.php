<?php

namespace Tcds\Io\Serializer\Metadata\Parser;

use ReflectionClass;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use Tcds\Io\Generic\ArrayList;

class Annotation
{
    public static function param(ReflectionMethod|ReflectionFunctionAbstract $function, string $name): ?string
    {
        return Annotation::extract(
            docblock: $function->getDocComment(),
            pattern: sprintf('/@param\s+([^\n]+?)\s+\$%s/s', $name),
        );
    }

    /**
     * @param string $type
     * @return array{ 0: string, 0: list<string> }|null
     */
    public static function extractGenerics(string $type): ?array
    {
        if (str_ends_with($type, '[]')) {
            $type = sprintf('list<%s>', str_replace('[]', '', $type));
        }

        // check generics
        $pattern = '~^(.*?)<(.*?)>\s*$~';

        if (!preg_match($pattern, $type, $matches)) {
            return [$type, []];
        }

        return [
            $matches[1],
            array_map('trim', explode(',', $matches[2])),
        ];
    }

    public static function generic(ReflectionClass $reflection, string $type): string
    {
        [$type, $generics] = self::extractGenerics($type);
        $main = self::fqnOf($reflection, $type);

        // not generic
        if (empty($generics)) {
            return $main;
        }

        return generic(
            type: $main,
            generics: new ArrayList($generics)
                ->map(fn(string $generic) => self::fqnOf($reflection, $generic))
                ->items(),
        );
    }

    private static function extract(string $docblock, string $pattern): ?string
    {
        $docblock = trim($docblock ?: '');
        $docblock = preg_replace('/\/\*\*|\*\/|\*/', '', $docblock);
        $docblock = preg_replace('/\s*\n\s*/', ' ', $docblock);
        $docblock = join(PHP_EOL, array_map(fn(string $line) => "@$line", explode('@', $docblock)));
        preg_match($pattern, $docblock, $matches);

        return $matches[1] ?? null;
    }

    /**
     * @return array{0, string, 1: array<string, string>}
     */
    public static function shaped(string $shape): array
    {
        preg_match_all('/(\w+)\s*:\s*([^,\s}]+)/', $shape, $pairs, PREG_SET_ORDER);

        $params = [];

        foreach ($pairs as $pair) {
            $name = $pair[1];
            $params[$name] = $pair[2];
        }

        $type = match (true) {
            str_starts_with($shape, 'object') => 'object',
            default => 'array',
        };

        return [$type, $params];
    }

    /**
     * @return array{0, string, 1: list<string>}
     */
    public static function shapedFqn(ReflectionClass $class, string $shape): array
    {
        [$type, $namedParams] = self::shaped($shape);

        $params = [];

        foreach ($namedParams as $name => $paramType) {
            $paramType = Annotation::fqnOf($class, $paramType);

            $params[] = "$name: $paramType";
        }

        return [$type, $params];
    }

    private static function fqnOf(ReflectionClass $class, string $name): string
    {
        if (ParamType::isResolvedType($name)) {
            return $name;
        }

        $source = file_get_contents($class->getFileName());
        $fqn = $class->getNamespaceName() . '\\' . $name;
        $pattern = sprintf("/use\s(.*?)%s;/", $name);

        if (preg_match($pattern, $source, $matches)) {
            $fqn = $matches[1] . $name;
        }

        if (class_exists($fqn) || enum_exists($fqn)) {
            return $fqn;
        }

        return $name;
    }
}
