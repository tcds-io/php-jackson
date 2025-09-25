<?php

namespace Tcds\Io\Serializer\Runtime;

use Override;
use Tcds\Io\Serializer\Metadata\TypeNode;
use Tcds\Io\Serializer\Metadata\TypeNodeRepository;

readonly class RuntimeTypeNodeRepository implements TypeNodeRepository
{
    #[Override] public function of(string $type): TypeNode
    {
        return TypeNode::from($type);
    }
}
