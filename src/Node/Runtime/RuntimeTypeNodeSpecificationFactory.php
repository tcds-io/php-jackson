<?php

namespace Tcds\Io\Jackson\Node\Runtime;

use BackedEnum;
use Tcds\Io\Generic\Reflection\Type\ReflectionType;
use Tcds\Io\Jackson\Exception\JacksonException;
use Tcds\Io\Jackson\Node\InputNode;
use Tcds\Io\Jackson\Node\TypeNode;
use Tcds\Io\Jackson\Node\TypeNodeFactory;
use Tcds\Io\Jackson\Node\TypeNodeSpecificationFactory;

class RuntimeTypeNodeSpecificationFactory implements TypeNodeSpecificationFactory
{
    /** @var array<string, bool> */
    private array $specifications = [];

    public function __construct(
        private readonly TypeNodeFactory $factory = new RuntimeTypeNodeFactory(),
    ) {}

    public function create(TypeNode|string $node): array|string
    {
        if (is_string($node)) {
            $node = $this->factory->create($node);
        }

        if (array_key_exists($node->type, $this->specifications)) {
            return [];
        }

        return match (true) {
            ReflectionType::isPrimitive($node->type) => $node->type,
            ReflectionType::isEnum($node->type) => array_map(fn(BackedEnum $enum) => $enum->value, $node->type::cases()),
            ReflectionType::isList($node->type) => [$this->create($node->inputs[0]->type)],
            ReflectionType::isClass($node->type),
            ReflectionType::isShape($node->type) => run(function () use ($node) {
                $this->specifications[$node->type] = true;

                return listOf(...$node->inputs)
                    ->indexedBy(fn(InputNode $input) => $input->name)
                    ->mapValues(fn(InputNode $input) => $this->create($input->type))
                    ->entries();
            }),
            ReflectionType::isArray($node->type) => [
                $node->inputs[0]->type => $this->create($node->inputs[1]->type),
            ],
            default => throw new JacksonException(sprintf('Unable to load specification of type `%s`', $node->type)),
        };
    }
}
