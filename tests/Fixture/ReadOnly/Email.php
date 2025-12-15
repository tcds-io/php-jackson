<?php

namespace Test\Tcds\Io\Jackson\Fixture\ReadOnly;

readonly class Email
{
    public function __construct(public string $value) {}
}
