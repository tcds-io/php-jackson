<?php

namespace Tcds\Io\Serializer\Unit\Exception;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tcds\Io\Serializer\Exception\UnableToParseValue;

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

        $this->assertEquals('Unable to parse value at address.place.position', $exception->getMessage());
        $this->assertEquals(['lat' => 'float', 'lng' => 'float'], $exception->expected);
        $this->assertEquals('-26.9013, -48.6655', $exception->given);
    }
}
