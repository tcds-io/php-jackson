<?php

/**
 * @return class-string<object>
 * @internal
 */
function asClassString(string $value): string
{
    /** @var string */
    return $value;
}

/**
 * @internal
 */
function asStringOrInt(mixed $value): string|int
{
    /** @var string|int */
    return $value;
}

/**
 * @return array<mixed>
 * @internal
 */
function asArray(mixed $value): array
{
    /** @var array<mixed> */
    return $value;
}
