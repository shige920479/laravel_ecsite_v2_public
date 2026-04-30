<?php

namespace App\Services\Customer\Order\Exceptions;

use App\Exceptions\ApiException;
use Exception;

class NotRegiteredAccountException extends ApiException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'NOT_REGISTERED_ACCOUNT',
            message: 'アカウント情報の登録が完了しておりません。「メニュー」 > 「アカント情報」 から登録願います',
            status: 405,
        );
    }
}
