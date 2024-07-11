<?php

namespace App\Exception;

use Exception;
use Throwable;

class ForbiddenException extends Exception implements ExceptionInterface
{
    public function __construct(string $message = "Access denied", int $code = 403, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}