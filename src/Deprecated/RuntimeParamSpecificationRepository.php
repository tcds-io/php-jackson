<?php

namespace Tcds\Io\Serializer\Deprecated;

use Override;
use ReflectionClass;
use ReflectionParameter;
use Tcds\Io\Generic\ArrayList;
use Tcds\Io\Serializer\Param\ParamSpecification;
use Tcds\Io\Serializer\Param\ParamSpecificationRepository;

/**
 * @phpstan-import-type ParamName from ParamSpecificationRepository
 */
readonly class RuntimeParamSpecificationRepository implements ParamSpecificationRepository
{
    /**
     * @template T
     * @param class-string<T> $class
     * @return array<ParamName, ParamSpecification>
     */
    #[Override] public function of(string $class): array
    {
        return new ArrayList(new ReflectionClass($class)->getConstructor()->getParameters())
            ->indexedBy(fn(ReflectionParameter $param) => $param->name)
            ->mapValues($this->specification(...))
            ->entries();
    }

    private function specification(ReflectionParameter $param): ParamSpecification
    {
        $type = Generic::from($param);

        return new RuntimeParamSpecification(
            name: $param->name,
            type: $type,
            default: $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null,
        );
    }
}
