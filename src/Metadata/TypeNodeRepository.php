<?php

namespace Tcds\Io\Serializer\Metadata;

interface TypeNodeRepository
{
    public function of(string $type): TypeNode;
}
