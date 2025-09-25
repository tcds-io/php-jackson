<?php

declare(strict_types=1);

namespace Tcds\Io\Serializer\Exception;

class ArrayPropertyMustHaveATypeAnnotation extends SerializerException
{
    public function __construct(string $param, string $class)
    {
        parent::__construct("Array property $class::$param must have an array annotation");
    }
}
