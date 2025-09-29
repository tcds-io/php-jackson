<?php

namespace Tcds\Io\Serializer;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use Tcds\Io\Serializer\Metadata\TypeNode;
use Throwable;

abstract class SerializerTestCase extends TestCase
{
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

    protected function initializeLazyParams(TypeNode $node, int $depth = 10): void
    {
        if ($depth < 1) {
            return;
        }

        $depth = $depth - 1;

        foreach ($node->params as $param) {
            initializeLazyObject($param);
            $this->initializeLazyParams($param->node, $depth);
        }
    }
}
