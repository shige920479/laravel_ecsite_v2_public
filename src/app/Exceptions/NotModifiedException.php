<?php

namespace App\Exceptions;

class NotModifiedException extends ApiException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'NOT_MODIFIED',
            message: '入力内容に変更がありません、再度入力願います',
            status: 409,
        );
    }
}
