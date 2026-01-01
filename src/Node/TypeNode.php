<?php

namespace Tcds\Io\Jackson\Node;

use Tcds\Io\Generic\Reflection\Type\ReflectionType;

final class TypeNode
{
    /** @var list<OutputNode> */
    public array $outputs = [];

    /**
     * @param list<InputNode> $inputs
     * @param list<OutputNode|null> $outputs
     */
    public function __construct(
        public string $type,
        public array $inputs = [],
        array $outputs = [],
    ) {
        $this->outputs = self::filterOutputs($outputs);
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

    public function isValueObject(): bool
    {
        return count($this->outputs) === 1
            && $this->outputs[0]->name === 'value'
            && ReflectionType::isPrimitive($this->outputs[0]->type);
    }

    /**
     * @param list<OutputNode|null> $outputs
     * @return list<OutputNode>
     */
    private static function filterOutputs(array $outputs): array
    {
        /** @var list<OutputNode> */
        return array_filter($outputs, static fn ($v) => $v !== null);
    }
}
