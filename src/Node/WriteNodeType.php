<?php

declare(strict_types=1);

namespace Tcds\Io\Serializer\Node;

enum WriteNodeType: string
{
    case PROPERTY = 'property';
    case METHOD = 'method';
}
