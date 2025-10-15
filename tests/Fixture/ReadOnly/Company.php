<?php

declare(strict_types=1);

namespace Tcds\Io\Serializer\Fixture\ReadOnly;

readonly class Company
{
    /**
     * @param list<Address> $addresses
     */
    public function __construct(
        private string $businessName,
        private string $registrationName,
        private bool $active,
        private array $addresses,
    ) {
    }

    /**
     * @param list<Address> $addresses
     */
    public static function create(string $name, bool $active, array $addresses): self
    {
        preg_match('/^(.*?)\s*\((.*?)\)$/', $name, $matches);
        [, $businessName, $registrationName] = $matches;

        return new self($businessName, $registrationName, $active, $addresses);
    }

    public function name(): string
    {
        return "$this->businessName ($this->registrationName)";
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @return list<Address>
     */
    public function getAddresses(): array
    {
        return $this->addresses;
    }
}
