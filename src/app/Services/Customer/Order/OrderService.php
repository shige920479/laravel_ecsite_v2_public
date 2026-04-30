<?php
namespace App\Services\Customer\Order;

use App\Models\CheckoutRequest;
use App\Models\Order;
use App\Services\Customer\Order\DTO\StoreOrderCommand;
use Stripe\Event;

class OrderService implements OrderServiceInterface
{
    public function createOrder(CheckoutRequest $checkoutRequest, Event $event): Order
    {
        $order = new Order();
        $order->fillFromDto(StoreOrderCommand::fromCheckoutRequest($checkoutRequest, $event));
        $order->save();

        $order->order_number = "ORD-" . now()->format('Ymd') . '-' . str_pad($order->id, 5, '0', STR_PAD_LEFT);
        $order->save();

        return $order;
    }
}