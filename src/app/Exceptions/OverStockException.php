<?php
namespace App\Exceptions;

class OverStockException extends ApiException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'CONFLICT',
            message: '在庫数量を超えております',
            status: 409,
        );
    }
}