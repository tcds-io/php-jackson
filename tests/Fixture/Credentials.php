<?php

namespace Test\Tcds\Io\Jackson\Fixture;

use Test\Tcds\Io\Jackson\Fixture\ReadOnly\User;

readonly class Credentials
{
    public function __construct(
        public User $user,
        private string $login,
        private string $password,
        private bool $valid,
        private bool $expired,
    ) {
    }

    public function login(): string
    {
        return $this->login;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function hasExpired(): bool
    {
        return $this->expired;
    }

    public static function value(): self
    {
        return new self(
            user: User::arthurDent(),
            login: 'arthur',
            password: 'dent',
            valid: true,
            expired: false,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function data(): array
    {
        return [
            'user' => User::arthurDentData(),
            'login' => 'arthur',
            'password' => 'dent',
            'valid' => true,
            'expired' => false,
        ];
    }
}
