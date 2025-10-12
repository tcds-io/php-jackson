<?php

declare(strict_types=1);

namespace Tcds\Io\Serializer\Metadata;

enum OutputNodeType: string
{
    case PROPERTY = 'property';
    case METHOD = 'method';
}
