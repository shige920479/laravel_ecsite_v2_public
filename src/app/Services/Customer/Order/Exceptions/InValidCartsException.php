<?php

namespace App\Services\Customer\Order\Exceptions;

use App\Exceptions\ApiException;
use Exception;

class InValidCartsException extends ApiException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'INVALID_CARTS',
            message: 'カート情報が正しくありません、ご確認願います',
            status: 400,
        );
    }
}
