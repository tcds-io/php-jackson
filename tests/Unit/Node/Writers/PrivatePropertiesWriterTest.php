<?php

namespace Test\Tcds\Io\Jackson\Unit\Node\Writers;

use PHPUnit\Framework\Attributes\Test;
use Test\Tcds\Io\Jackson\Fixture\Credentials;
use Test\Tcds\Io\Jackson\SerializerTestCase;

class PrivatePropertiesWriterTest extends SerializerTestCase
{
    #[Test]
    public function write_class_with_private_properties(): void
    {
        $value = Credentials::value();

        $data = $this->arrayMapper->writeValue($value);

        $this->assertEquals(Credentials::data(), $data);
    }
}
