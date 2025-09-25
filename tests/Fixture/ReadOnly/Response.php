<?php

namespace Tcds\Io\Serializer\Fixture\ReadOnly;

readonly class Response
{
    /**
     * @param array<string, mixed> $_meta
     */
    public function __construct(
        public array $_meta,
    ) {
    }

    /**
     * @return self<list<Address>>
     */
    public static function firstPage(): self
    {
        return new self(
            _meta: [
                'page' => 1,
                'totalPages' => 10,
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function data(): array
    {
        return [
            '_meta' => [
                'page' => 1,
                'totalPages' => 10,
            ],
        ];
    }
}
