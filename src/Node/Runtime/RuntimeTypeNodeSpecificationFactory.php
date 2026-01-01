<?php

namespace Tcds\Io\Jackson\Node\Runtime;

use BackedEnum;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Override;
use Tcds\Io\Generic\Reflection\Type\ReflectionType;
use Tcds\Io\Jackson\Exception\JacksonException;
use Tcds\Io\Jackson\Node\InputNode;
use Tcds\Io\Jackson\Node\TypeNode;
use Tcds\Io\Jackson\Node\TypeNodeFactory;
use Tcds\Io\Jackson\Node\TypeNodeSpecificationFactory;

class RuntimeTypeNodeSpecificationFactory implements TypeNodeSpecificationFactory
{
    /** @var array<string, bool> */
    private array $defined = [];

    /** @var array<string, mixed> */
    private array $specifications;

    /**
     * @param array<string, mixed> $specifications
     */
    public function __construct(
        private readonly TypeNodeFactory $factory = new RuntimeTypeNodeFactory(),
        array $specifications = [],
    ) {
        $this->specifications = [
            DateTime::class => 'datetime',
            DateTimeImmutable::class => 'datetime',
            DateTimeInterface::class => 'datetime',
            'Carbon\Carbon' => 'datetime',
            'Carbon\CarbonImmutable' => 'datetime',
            ...$specifications,
        ];
    }

    #[Override]
    public function create(TypeNode|string $node): mixed
    {
        if (is_string($node)) {
            $node = $this->factory->create($node);
        }

        if (array_key_exists($node->type, $this->defined)) {
            return [];
        }

        return match (true) {
            isset($this->specifications[$node->type]) => $this->specifications[$node->type],
            ReflectionType::isPrimitive($node->type) => $node->type,
            ReflectionType::isEnum($node->type) => array_map(fn (BackedEnum $enum) => $enum->value, $node->type::cases()),
            ReflectionType::isList($node->type) => [$this->create($node->inputs[0]->type)],
            ReflectionType::isGeneric($node->type) && !ReflectionType::isArray($node->type),
            ReflectionType::isClass($node->type),
            ReflectionType::isShape($node->type) => run(function () use ($node) {
                $this->defined[$node->type] = true;

                return listOf(...$node->inputs)
                    ->indexedBy(fn (InputNode $input) => $input->name)
                    ->mapValues(fn (InputNode $input) => $this->create($input->type))
                    ->entries();
            }),
            ReflectionType::isArray($node->type) => [
                $node->inputs[0]->type => $this->create($node->inputs[1]->type),
            ],
            default => throw new JacksonException(sprintf('Unable to load specification of type `%s`', $node->type)),
        };
    }
}
