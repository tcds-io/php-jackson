<?php

declare(strict_types=1);

namespace Tcds\Io\Serializer\Node;

enum OutputType: string
{
    case PROPERTY = 'property';
    case PARAM = 'param';
    case METHOD = 'method';
}
