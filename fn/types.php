<?php

/**
 * @return class-string<object>
 */
function asClassString(string $value): string
{
    /** @var string */
    return $value;
}

/**
 */
function asStringOrInt(mixed $value): string|int
{
    /** @var string|int */
    return $value;
}

/**
 * @return array<mixed>
 */
function asArray(mixed $value): array
{
    /** @var array<mixed> */
    return $value ?: [];
}
