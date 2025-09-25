<?php

declare(strict_types=1);

namespace Tcds\Io\Serializer\Fixture\ReadOnly;

use Tcds\Io\Serializer\Fixture\AccountType;
use Tcds\Io\Serializer\Metadata\ParamNode;
use Tcds\Io\Serializer\Metadata\TypeNode;

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

    public static function node(): TypeNode
    {
        return new TypeNode(
            type: BankAccount::class,
            params: [
                'number' => new ParamNode(new TypeNode('string')),
                'type' => new ParamNode(new TypeNode(AccountType::class)),
            ],
        );
    }

    public static function fingerprint(): string
    {
        return sprintf('%s[%s, %s]', self::class, 'string', AccountType::class);
    }
}
