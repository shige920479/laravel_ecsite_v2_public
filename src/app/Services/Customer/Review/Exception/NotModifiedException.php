<?php

namespace App\Services\Customer\Review\Exception;

use App\Exceptions\ApiException;

class NotModifiedException extends ApiException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'NOT_MODIFIED',
            message: '投稿内容に変更がありません、再度入力願います',
            status: 409,
        );
    }
}
