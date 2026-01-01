<?php

declare(strict_types=1);

namespace Tcds\Io\Jackson\Exception;

use Exception;
use Throwable;

class JacksonException extends Exception
{
    /**
     * @param list<string> $trace
     */
    public function __construct(string $message = '', public readonly array $trace = [], ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
