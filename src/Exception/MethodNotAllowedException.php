<?php

namespace App\Exception;

use Exception;
use Throwable;

class MethodNotAllowedException extends Exception implements ExceptionInterface
{
    public function __construct(string $message = "Invalid HTTP method", int $code = 405, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}