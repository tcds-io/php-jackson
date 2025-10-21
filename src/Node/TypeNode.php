<?php

namespace Tcds\Io\Serializer\Node;

final class TypeNode
{
    /**
     * @param list<InputNode> $inputs
     * @param list<OutputNode> $outputs
     */
    public function __construct(
        public string $type,
        public array $inputs = [],
        public array $outputs = [],
    ) {
    }

    public static function of(mixed $data): string
    {
        return match (true) {
            is_object($data) => $data::class,
            is_array($data) => run(function () use ($data) {
                $value = self::of(reset($data));
                $key = self::of(array_key_first($data));

                return array_is_list($data)
                    ? sprintf('list<%s>', $value)
                    : sprintf('map<%s, %s>', $key, $value);
            }),
            default => gettype($data),
        };
    }
}
