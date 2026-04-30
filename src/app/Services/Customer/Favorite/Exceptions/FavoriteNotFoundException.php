<?php

namespace App\Services\Customer\Favorite\Exceptions;

use App\Exceptions\ApiException;

class FavoriteNotFoundException extends ApiException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'FAVORITE_NOT_FOUND',
            message: '商品がお気に入りリストにありません。再度お試しください',
            status: 404,
        );
    }
}
