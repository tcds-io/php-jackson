<?php

namespace Tcds\Io\Serializer\Exception;

class UnableToParseValue extends SerializerException
{
    public function __construct(array $trace, public mixed $expected, public mixed $given)
    {
        parent::__construct(sprintf('Unable to parse value at %s', join('.', $trace)));
    }
}
