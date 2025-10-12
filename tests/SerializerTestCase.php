<?php

namespace Tcds\Io\Serializer;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use Tcds\Io\Serializer\Metadata\Node\ReadNode;
use Tcds\Io\Serializer\Metadata\TypeNode;
use Throwable;

abstract class SerializerTestCase extends TestCase
{
    protected ArrayObjectMapper $arrayMapper;
    protected JsonObjectMapper $jsonMapper;

    protected function setUp(): void
    {
        TypeNode::$nodes = [];
        TypeNode::$specifications = [];

        $this->arrayMapper = new ArrayObjectMapper();
        $this->jsonMapper = new JsonObjectMapper();
    }

    /**
     * @template E of Throwable
     * @param class-string<E> $expected
     * @param callable $action
     * @return E
     */
    public function expectThrows(string $expected, callable $action)
    {
        try {
            $action();
        } catch (AssertionFailedError $e) {
            throw $e;
        } catch (Throwable $exception) {
            Assert::assertInstanceOf($expected, $exception);

            return $exception;
        }

        throw new AssertionFailedError('Failed asserting that an exception was thrown');
    }

    /**
     * @param list<ReadNode> $nodes
     */
    protected function initializeReadNodes(array $nodes): void
    {
        foreach ($nodes as $input) {
            $this->initializeNode($input->node);
        }
    }

    protected function initializeNode(TypeNode $node, int $depth = 10): void
    {
        if ($depth < 1) {
            return;
        }

        $depth = $depth - 1;

        foreach ($node->inputs as $param) {
            initializeLazyObject($param);
            $this->initializeNode($param->node, $depth);
        }
    }
}
