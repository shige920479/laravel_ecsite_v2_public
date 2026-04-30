<?php

namespace App\Services\Customer\Order\Exceptions;

use App\Exceptions\ApiException;

class NotFoundCheckoutRequestException extends ApiException
{
    public function __construct(
        public ?string $eventId = null
    )
    {
        parent::__construct(
            errorCode: 'CHECKOUT_REQUEST_NOT_FOUND',
            message: "チェックアウト情報が見つかりません: event_id: {$eventId}",
            status: 404,
        );
    }
}
