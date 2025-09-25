<?php

/**
 * @param list<string> $generics
 */
function generic(string $type, array $generics): string
{
    return empty($generics)
        ? $type
        : sprintf('%s<%s>', $type, join(', ', $generics));
}

/**
 * @param array<string, string> $params
 */
function shape(string $type, array $params): string
{
    $values = [];

    foreach ($params as $name => $param) {
        $values[] = "$name: $param";
    }

    return sprintf('%s{ %s }', $type, join(', ', $values));
}
