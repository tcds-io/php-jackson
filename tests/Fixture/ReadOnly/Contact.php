<?php

namespace Test\Tcds\Io\Jackson\Fixture\ReadOnly;

readonly class Contact
{
    public function __construct(public Email $email) {}
}
