<?php

namespace App\Exceptions;

use Exception;

class GoogleAccountMismatchException extends Exception
{
    public function __construct()
    {
        parent::__construct('Googleアカウントが一致していません。連携済みのGoogleアカウントでログインしてください。');
    }
}
