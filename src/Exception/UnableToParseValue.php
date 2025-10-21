<?php

namespace Tcds\Io\Serializer\Exception;

use Throwable;

class UnableToParseValue extends SerializerException
{
    public mixed $given;

    /**
     * @param list<string> $trace
     */
    public function __construct(public array $trace, public mixed $expected, mixed $given, ?Throwable $previous = null)
    {
        parent::__construct(sprintf('Unable to parse value at .%s', join('.', $trace)), $previous);

        $this->given = $this->toType($given);
    }

    /**
     * @param mixed $value
     * @return string|array<mixed>
     */
    private function toType(mixed $value): string|array
    {
        return match (true) {
            is_int($value) => 'int',
            is_float($value) => 'float',
            is_bool($value) => 'bool',
            is_null($value) => 'null',
            is_string($value) => match (true) {
                preg_match('/^[+-]?\d+$/', $value) === 1 => 'int',
                in_array(strtolower($value), ['true', 'false']) => 'bool',
                is_numeric($value) => 'float',
                default => 'string',
            },
            is_array($value) => array_map(fn ($inner) => $this->toType($inner), $value),
            is_object($value) => array_map(fn ($inner) => $this->toType($inner), get_object_vars($value)),
            default => get_debug_type($value),
        };
    }
}
