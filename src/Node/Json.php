<?php

declare(strict_types=1);

namespace Tcds\Io\Jackson\Node;

class Json
{
    public static function encode(mixed $value): string
    {
        return json_encode($value, JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<string, mixed>|list<mixed>
     */
    public static function decode(mixed $value): mixed
    {
        /** @var string $value */
        /** @var array<string, mixed>|list<mixed> */
        return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
    }
}
