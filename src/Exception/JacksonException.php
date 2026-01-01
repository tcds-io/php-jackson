<?php

declare(strict_types=1);

namespace Tcds\Io\Jackson\Exception;

use Exception;
use Throwable;

class JacksonException extends Exception
{
    /**
     * @param list<string> $path
     */
    public function __construct(string $message = '', public readonly array $path = [], ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
