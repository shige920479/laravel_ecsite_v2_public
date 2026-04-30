<?php

namespace App\Exceptions;

class SalesSuspendedException extends ApiException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'SALES_SUSPENDED',
            message: 'この商品は販売停止中です',
            status: 400,
        );
    }
}
