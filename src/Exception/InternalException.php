<?php

namespace App\Exception;

use Exception;
use Throwable;

class InternalException extends Exception implements ExceptionInterface
{
    public function __construct(string $message = "Internal error", int $code = 500, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}