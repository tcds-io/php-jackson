<?php

namespace Tcds\Io\Serializer\Metadata\Node;

readonly class ReadNode
{
    /**
     * @param string $name
     * @param string $type
     */
    public function __construct(
        public string $name,
        public string $type,
    ) {
    }

//    /**
//     * @template T
//     * @param class-string<T> $type
//     * @return list<ReadNode>
//     */
//    public static function of(string $type): array
//    {
//        [$type, $templates] = TypeResolver::from($type);
//
//        return new ArrayList(
//            Reflection::of(class: $type)
//                ->getConstructor()
//                ->getParameters(),
//        )->map(function (ReflectionParameter $param) use ($templates) {
//            $paramType = Type::ofParam($param);
//            $paramType = $templates[$paramType] ?? $paramType;
//            [$paramType, $paramGenerics] = Annotation::extractGenerics($paramType);
//
//            foreach ($paramGenerics as $index => $paramGeneric) {
//                $paramGenerics[$index] = $templates[$index] ?? $templates[$paramGeneric] ?? $paramGeneric;
//            }
//
//            return ReadNode::from($param->name, generic($paramType, $paramGenerics));
//        })
//            ->indexedBy(fn(ReadNode $node) => $node->name)
//            ->entries();
//    }
}
