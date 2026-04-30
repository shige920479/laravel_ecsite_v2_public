<?php

namespace App\Exceptions;

use Exception;

class ApiException extends Exception
{
    public function __construct(
        public string $errorCode,
        string $message,
        public int $status = 400,
    ) {
        parent::__construct($message);
    }
}
