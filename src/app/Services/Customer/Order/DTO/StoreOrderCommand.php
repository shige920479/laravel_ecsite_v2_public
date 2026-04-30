<?php
namespace App\Services\Customer\Order\DTO;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\CheckoutRequest;
use Stripe\Event;

class StoreOrderCommand
{
    public function __construct(
        public int $userId,
        public int $totalExTax,
        public int $totalTax,
        public int $totalInTax,
        public PaymentMethod $paymentMethod,
        public string $stripeSessionId,
        public OrderStatus $paymentStatus,
        public string $orderedAt,
    )
    {
    }

    public static function fromCheckoutRequest(CheckoutRequest $checkoutRequest, Event $event): self
    {
        return new self(
            userId: $checkoutRequest->user_id,
            totalExTax: $checkoutRequest->total_ex_tax,
            totalTax: $checkoutRequest->total_tax,
            totalInTax: $checkoutRequest->total_in_tax,
            paymentMethod: PaymentMethod::CARD,
            stripeSessionId: $event->data->object->id,
            paymentStatus: OrderStatus::PAID,
            orderedAt: $checkoutRequest->created_at,
        );
    }
}