<?php

namespace Tcds\Io\Serializer\Deprecated;

use BackedEnum;
use Override;
use Tcds\Io\Serializer\Param\ParamSpecification;
use Tcds\Io\Serializer\Param\ParamSpecificationRepository;

/**
 * @deprecated
 */
class RuntimeParamSpecification implements ParamSpecification
{
    public function __construct(
        public readonly string $name,
        public readonly Generic $type,
        public readonly mixed $default,
        private readonly ParamSpecificationRepository $specifications = new RuntimeParamSpecificationRepository(),
    ) {
    }

    public bool $isClass {
        get => class_exists($this->type->resolved);
    }

    public bool $isList {
        get => $this->type->resolved === 'list';
    }

    public bool $isEnum {
        get => enum_exists($this->type->resolved);
    }

    public bool $isBoolean {
        get => $this->type->resolved === 'bool';
    }

    #[Override] public function listType(): self
    {
        return new self(
            $this->name,
            $this->type->toListGenericType(),
            null,
            $this->specifications,
        );
    }

    #[Override] public function enumCases(): array
    {
        /** @var BackedEnum $type */
        $type = $this->type->resolved;

        return $type::cases();
    }

    #[Override] public function enumFrom(int|string $data)
    {
        /** @var BackedEnum $type */
        $type = $this->type->resolved;

        return $type::tryFrom($data);
    }

    #[Override] public function definition(): mixed
    {
        $name = $this->type->resolved ?? null;

        return match (true) {
            $this->isList => [$this->listType()->definition()],
            $this->isEnum => $this->enumCases(),
            $this->isClass => array_map(fn($s) => $s->definition(), $this->specifications->of($name)),
            default => $name,
        };
    }
}
