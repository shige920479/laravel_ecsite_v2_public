<?php

namespace App\Exceptions;

use Exception;

class GoogleAuthenticationException extends Exception
{
    public function __construct()
    {
        parent::__construct('Google認証に失敗しました。再度お試しください');
    }
}
