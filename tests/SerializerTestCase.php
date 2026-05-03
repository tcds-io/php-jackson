<?php

namespace Test\Tcds\Io\Jackson;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use Tcds\Io\Jackson\ArrayObjectMapper;
use Tcds\Io\Jackson\JsonObjectMapper;
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
}
