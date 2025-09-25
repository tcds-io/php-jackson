<?php

namespace Tcds\Io\Serializer;

readonly class ArrayObjectMapper extends ObjectMapper
{
    /**
     * @template T
     * @param class-string<T> $type
     * @param array<string, mixed> $value
     * @param array<string, mixed> $with
     * @return T
     */
    public function readValueWith(string $type, mixed $value, array $with = [])
    {
        return $this->readValue($type, [
            ...$value,
            ...$with,
        ]);
    }
}
