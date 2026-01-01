<?php

namespace Test\Tcds\Io\Jackson\Unit\Exception;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tcds\Io\Jackson\Exception\UnableToParseValue;

class UnableToParseValueTest extends TestCase
{
    #[Test] public function exception_props(): void
    {
        $exception = new UnableToParseValue(
            ['address', 'place', 'position'],
            [
                'lat' => 'float',
                'lng' => 'float',
            ],
            '-26.9013, -48.6655',
        );

        $this->assertEquals('Unable to parse value at .address.place.position', $exception->getMessage());
        $this->assertEquals(['lat' => 'float', 'lng' => 'float'], $exception->expected);
        $this->assertEquals('string', $exception->given);
    }

    #[Test] public function map_given_to_types(): void
    {
        $exception = new UnableToParseValue(
            path: [],
            expected: [],
            given: [
                'null-value' => null,
                'int-value' => 45,
                'int-string' => '32',
                'float-value' => 29.99,
                'float-string' => '89.12',
                'boolean-value-true' => true,
                'boolean-value-false' => false,
                'boolean-string-true' => 'true',
                'boolean-string-false' => 'false',
                'boolean-string-upper-true' => 'TRUE',
                'boolean-string-upper-false' => 'FALSE',
                'string-value' => 'Thiago Cordeiro',
                'array-value' => [
                    'inner-array-int-value' => 45,
                    'inner-array-int-string' => '32',
                    'inner-array-float-value' => 29.99,
                    'inner-array-float-string' => '89.12',
                    'inner-array-boolean-value-true' => true,
                    'inner-array-boolean-value-false' => false,
                ],
                'object-value' => (object) [
                    'inner-object-int-value' => 45,
                    'inner-object-int-string' => '32',
                    'inner-object-float-value' => 29.99,
                    'inner-object-float-string' => '89.12',
                    'inner-object-boolean-value-true' => true,
                    'inner-object-boolean-value-false' => false,
                ],
            ],
        );

        $this->assertEquals(
            [
                'null-value' => 'null',
                'int-value' => 'int',
                'int-string' => 'int',
                'float-value' => 'float',
                'float-string' => 'float',
                'boolean-value-true' => 'bool',
                'boolean-value-false' => 'bool',
                'boolean-string-true' => 'bool',
                'boolean-string-false' => 'bool',
                'boolean-string-upper-true' => 'bool',
                'boolean-string-upper-false' => 'bool',
                'string-value' => 'string',
                'array-value' => [
                    'inner-array-int-value' => 'int',
                    'inner-array-int-string' => 'int',
                    'inner-array-float-value' => 'float',
                    'inner-array-float-string' => 'float',
                    'inner-array-boolean-value-true' => 'bool',
                    'inner-array-boolean-value-false' => 'bool',
                ],
                'object-value' => [
                    'inner-object-int-value' => 'int',
                    'inner-object-int-string' => 'int',
                    'inner-object-float-value' => 'float',
                    'inner-object-float-string' => 'float',
                    'inner-object-boolean-value-true' => 'bool',
                    'inner-object-boolean-value-false' => 'bool',
                ],
            ],
            $exception->given,
        );
    }
}
