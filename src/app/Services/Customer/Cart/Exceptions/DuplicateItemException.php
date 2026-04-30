<?php

namespace App\Services\Customer\Cart\Exceptions;

use App\Exceptions\ApiException;

class DuplicateItemException extends ApiException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'CART_DUPLICATE_ITEM',
            message: '同じ商品が既にカートに存在します',
            status: 409,
        );
    }
}
