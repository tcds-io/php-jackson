<?php

namespace Tcds\Io\Jackson\Node;

readonly class InputNode
{
    public function __construct(
        public string $name,
        public string $type,
        public mixed $default,
        public ?string $key = null,
    ) {
    }

    public function key(): string
    {
        return $this->key ?? $this->name;
    }
}
