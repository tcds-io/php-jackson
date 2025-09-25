<?php

namespace Tcds\Io\Serializer;

readonly class JsonObjectMapper extends ObjectMapper
{
    /**
     * @template T
     * @param class-string<T> $type
     * @return T
     */
    public function readValueWith(string $type, string $value, array $with = [])
    {
        return $this->readValue($type, [
            ...json_decode($value, true),
            ...$with,
        ]);
    }
}
