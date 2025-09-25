<?php

namespace Tcds\Io\Serializer;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use Throwable;

class SerializerTestCase extends TestCase
{
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
