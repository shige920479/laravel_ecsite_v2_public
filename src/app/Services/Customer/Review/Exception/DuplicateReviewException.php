<?php

namespace App\Services\Customer\Review\Exception;

use App\Exceptions\ApiException;

class DuplicateReviewException extends ApiException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'REVIEW_DUPLICATE_ITEM',
            message: 'この商品へは既にレビューを投稿済です',
            status: 409,
        );
    }
}
