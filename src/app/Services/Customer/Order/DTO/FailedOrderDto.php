<?php
namespace App\Services\Customer\Order\DTO;

use App\Models\CheckoutRequest;
use App\Models\Order;
use Illuminate\Support\Collection;

class FailedOrderDto
{
    public function __construct(
        public CheckoutRequest $checkoutRequest,
        public Collection $checkoutItems,
    )
    {
    }
}