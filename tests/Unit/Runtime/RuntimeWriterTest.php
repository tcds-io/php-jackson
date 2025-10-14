<?php

namespace Tcds\Io\Serializer\Unit\Runtime;

use PHPUnit\Framework\Attributes\Test;
use Tcds\Io\Serializer\Fixture\ReadOnly\Address;
use Tcds\Io\Serializer\Runtime\RuntimeWriter;
use Tcds\Io\Serializer\SerializerTestCase;

class RuntimeWriterTest extends SerializerTestCase
{
    #[Test] public function given_x_when_x_then(): void
    {
        $writer = new RuntimeWriter();
        $address = Address::main();

        $data = $writer($address, Address::class, $this->arrayMapper);

        // dd($data);
    }
}
