<?php

namespace Tcds\Io\Serializer\Node;

readonly class OutputNode
{
    public function __construct(public string $name, public string $type, public OutputType $outputType)
    {
    }

    public static function property(string $name, string $type): self
    {
        return new self($name, $type, OutputType::PROPERTY);
    }

    public static function param(string $name, string $type): self
    {
        return new self($name, $type, OutputType::PARAM);
    }

    public static function method(string $name, string $type): self
    {
        return new self($name, $type, OutputType::METHOD);
    }

    public function read(mixed $data): mixed
    {
        return match ($this->outputType) {
            OutputType::PROPERTY => $data->{$this->name},
            OutputType::PARAM => $data[$this->name],
            OutputType::METHOD => $data->{$this->name}(),
        };
    }
}
