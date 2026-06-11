<?php

namespace App\Exceptions;

use Exception;

class ExistInDeletedAdmins extends Exception
{
    public function __construct()
    {
        parent::__construct('この管理者は権限を停止しています。権限を復帰してください');
    }
}
