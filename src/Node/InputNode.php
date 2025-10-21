<?php

namespace Tcds\Io\Serializer\Node;

readonly class InputNode
{
    public function __construct(public string $name, public string $type)
    {
    }
}
