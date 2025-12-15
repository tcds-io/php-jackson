<?php

namespace Tcds\Io\Jackson\Node;

readonly class OutputNode
{
    private function __construct(
        public string $name,
        public string $accessor,
        public string $type,
        public OutputType $outputType,
    ) {
    }

    public static function property(string $name, string $type): self
    {
        return new self(name: $name, accessor: $name, type: $type, outputType: OutputType::PROPERTY);
    }

    public static function param(string $name, string $type): self
    {
        return new self(name: $name, accessor: $name, type: $type, outputType: OutputType::PARAM);
    }

    public static function method(string $name, string $accessor, string $type): self
    {
        return new self(name: $name, accessor: $accessor, type: $type, outputType: OutputType::METHOD);
    }

    public function read(mixed $data): mixed
    {
        /** @var array<string, mixed>|object $data */
        return self::readFromData(type: $this->outputType, accessor: $this->accessor, data: $data);
    }

    /**
     * @param ($type is OutputType::PARAM ? array<string, mixed> : object) $data
     */
    private static function readFromData(OutputType $type, string $accessor, mixed $data): mixed
    {
        return match ($type) {
            OutputType::PARAM => $data[$accessor],
            OutputType::PROPERTY => $data->{$accessor},
            OutputType::METHOD => $data->{$accessor}(),
        };
    }
}
