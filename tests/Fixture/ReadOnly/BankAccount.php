<?php

declare(strict_types=1);

namespace Tcds\Io\Serializer\Fixture\ReadOnly;

use Tcds\Io\Serializer\Fixture\AccountType;

readonly class BankAccount
{
    public function __construct(
        public string $number,
        public AccountType $type,
    ) {
    }

    public static function checking(): self
    {
        return new self(
            number: '12345-X',
            type: AccountType::CHECKING,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function data(): array
    {
        return [
            'number' => '12345-X',
            'type' => 'checking',
        ];
    }
}
