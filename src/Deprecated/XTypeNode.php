<?php

namespace Tcds\Io\Serializer\Deprecated;

use ReflectionClass;
use ReflectionParameter;
use Tcds\Io\Generic\ArrayList;
use Tcds\Io\Serializer\Exception\SerializerException;

/**
 * @deprecated
 */
class XTypeNode
{
    /** @var array<string, XTypeNode> */
    public static array $nodes = [];

    /**
     * @param list<XParamNode> $params
     */
    public function __construct(
        public readonly string $type,
        public readonly array $params = [],
    ) {
    }

    /**
     * @param ArrayList<XTypeNode> $templates
     */
    public static function lazy(string $type, ArrayList $templates = new ArrayList([]))
    {
        return lazyOf(self::class, fn() => self::from($type, $templates));
    }

    /**
     * @param ArrayList<XTypeNode> $templates
     */
    public static function from(
        string $type,
        ArrayList $templates = new ArrayList([]),
    ): self {
        if (self::isResolvedType($type)) {
            return new XTypeNode($type);
        }

        if (!class_exists($type)) {
            throw new SerializerException("Type <$type> is not scalar nor an existing class");
        }

        if (array_key_exists($type, self::$nodes)) {
            return self::$nodes[$type];
        }

        $reflection = new ReflectionClass($type);
        $params = new ArrayList($reflection
            ->getConstructor()
            ->getParameters())
            ->map(fn(ReflectionParameter $param) => XParamNode::from($param, $templates))
            ->items();

        $node = new XTypeNode(type: $type, params: $params);
        $key = $node->describe();

        return self::$nodes[$key] = $node;
    }

    public static function isResolvedType(string $type): bool
    {
        if (enum_exists($type)) {
            return true;
        }

        $simpleNodeTypes = ['int', 'float', 'string', 'bool', 'boolean', 'object', 'mixed', 'list', 'map'];
        $types = explode('|', str_replace('&', '|', $type));

        $notScalar = array_filter($types, fn($t) => !in_array($t, $simpleNodeTypes, true));

        if (count($types) > 1 && !empty($notScalar)) {
            throw new SerializerException('Non-scalar union types are not allowed');
        }

        return empty($notScalar);
    }

    public function describe(): string
    {
        return sprintf(
            '%s(%s)',
            $this->type,
            join(',', array_map(fn(XParamNode $param) => $param->type, $this->params),
            ),
        );
    }
}
