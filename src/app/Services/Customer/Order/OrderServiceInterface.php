<?php
namespace App\Services\Customer\Order;

use App\Models\CheckoutRequest;
use App\Models\Order;
use Stripe\Event;

interface OrderServiceInterface
{
    public function createOrder(CheckoutRequest $checkoutRequest, Event $event): Order;
}