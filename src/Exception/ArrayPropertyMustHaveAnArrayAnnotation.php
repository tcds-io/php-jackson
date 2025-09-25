<?php

declare(strict_types=1);

namespace Tcds\Io\Serializer\Exception;

use ReflectionClass;
use ReflectionParameter;

class ArrayPropertyMustHaveAnArrayAnnotation extends SerializerException
{
    public function __construct(ReflectionParameter $param, ReflectionClass $class, string $type)
    {
        parent::__construct(
            sprintf(
                'Array property %s::%s must have an array annotation, use %s[] instead',
                $class->getName(),
                $param->getName(),
                $type,
            ),
        );
    }
}
