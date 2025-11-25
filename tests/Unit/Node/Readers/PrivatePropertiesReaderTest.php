<?php

namespace Test\Tcds\Io\Jackson\Unit\Node\Readers;

use PHPUnit\Framework\Attributes\Test;
use Test\Tcds\Io\Jackson\Fixture\Credentials;
use Test\Tcds\Io\Jackson\SerializerTestCase;

class PrivatePropertiesReaderTest extends SerializerTestCase
{
    #[Test]
    public function get_values_with_private_properties(): void
    {
        $data = Credentials::data();

        $credentials = $this->arrayMapper->readValue(Credentials::class, $data);

        $this->assertEquals(
            Credentials::value(),
            $credentials,
        );
    }
}
