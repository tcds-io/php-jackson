<?php

namespace Test\Tcds\Io\Jackson;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use Tcds\Io\Jackson\ArrayObjectMapper;
use Tcds\Io\Jackson\JsonObjectMapper;
use Tcds\Io\Jackson\Node\InputNode;
use Tcds\Io\Jackson\Node\TypeNode;
use Throwable;

abstract class SerializerTestCase extends TestCase
{
    protected ArrayObjectMapper $arrayMapper;
    protected JsonObjectMapper $jsonMapper;

    protected function setUp(): void
    {
        $this->arrayMapper = new ArrayObjectMapper();
        $this->jsonMapper = new JsonObjectMapper();
    }

    public function expectThrows(callable $action): Throwable
    {
        try {
            $action();
        } catch (AssertionFailedError $e) {
            throw $e;
        } catch (Throwable $exception) {
            return $exception;
        }

        throw new AssertionFailedError('Failed asserting that an exception was thrown');
    }

    /**
     * @param list<InputNode> $nodes
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
