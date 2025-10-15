<?php

declare(strict_types=1);

namespace Tcds\Io\Serializer\Reflection\Type;

use Tcds\Io\Serializer\Metadata\Parser\Annotation;
use Tcds\Io\Serializer\Reflection\ReflectionClass;

class ShapeReflectionType extends ReflectionType
{
    /**
     * @param array<string, string> $params
     */
    public function __construct(ReflectionClass $reflection, string $type, public readonly array $params)
    {
        parent::__construct($reflection, $type);
    }

    public static function from(ReflectionClass $reflection, string $type): self
    {
        [$type, $params] = Annotation::fqn($reflection, $type);

        return new self($reflection, $type, $params);
    }

    public function getName(): string
    {
        $params = mapOf($this->params)
            ->map(function ($name, $type) {
                return [$name, "$name: $type"];
            })
            ->entries();

        return sprintf('%s{ %s }', $this->type, join(', ', $params));
    }
}
