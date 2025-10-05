<?php

namespace Tcds\Io\Serializer\Metadata;

interface Writer
{
    public function __invoke(mixed $data);
}
