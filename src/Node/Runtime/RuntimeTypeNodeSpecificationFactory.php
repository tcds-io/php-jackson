<?php

namespace Tcds\Io\Serializer\Node\Runtime;

use BackedEnum;
use Tcds\Io\Generic\Reflection\Type\ReflectionType;
use Tcds\Io\Serializer\Exception\SerializerException;
use Tcds\Io\Serializer\Node\InputNode;
use Tcds\Io\Serializer\Node\TypeNodeFactory;
use Tcds\Io\Serializer\Node\TypeNodeSpecificationFactory;

class RuntimeTypeNodeSpecificationFactory implements TypeNodeSpecificationFactory
{
    /** @var array<string, bool> */
    private static array $specifications = [];

    public function __construct(
        private readonly TypeNodeFactory $factory = new RuntimeTypeNodeFactory(),
    ) {
    }

    public function create(string $type): array|string
    {
        if (array_key_exists($type, self::$specifications)) {
            return [];
        }

        $node = $this->factory->create($type);

        return match (true) {
            ReflectionType::isPrimitive($type) => $type,
            ReflectionType::isEnum($type) => array_map(fn(BackedEnum $enum) => $enum->value, $type::cases()),
            ReflectionType::isList($type) => generic('list', $this->create($node->inputs[0]->type)),
            ReflectionType::isClass($type),
            ReflectionType::isShape($type) => run(function () use ($type) {
                self::$specifications[$type] = true;

                return array_map(
                    fn(InputNode $input) => $this->create($input->type),
                    $this->factory->create($type)->inputs,
                );
            }),
            ReflectionType::isArray($type) => [
                $node->inputs[0]->type => $this->create($node->inputs['value']->type),
            ],
            default => throw new SerializerException(sprintf('Unable to load specification of type `%s`', $type)),
        };
    }
}
