<?php

namespace App\Exception;

use Exception;
use Throwable;

class BadRequestException extends Exception implements ExceptionInterface
{
    public function __construct(string $message = "Bad request", int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}