<?php

namespace Tcds\Io\Jackson\Node;

readonly class InputNode
{
    public function __construct(public string $name, public string $type)
    {
    }
}
