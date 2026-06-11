<?php
namespace App\Services\Customer\Order\Notification;

use App\Notifications\Order\CheckoutExpiredNotification;
use App\Notifications\Order\OrderCompletedForCustomerNotification;
use App\Notifications\Order\OrderReceivedForOwnerNotification;
use App\Notifications\Order\PaymentFailedNotification;
use App\Services\Customer\Order\DTO\FailedOrderDto;
use App\Services\Customer\Order\DTO\OrderProcessResultDto;
use App\Services\Customer\Order\Notification\OrderNotificationServiceInterface;
use Illuminate\Support\Facades\Log;

class OrderNotificationService implements OrderNotificationServiceInterface
{
    public function notifyCustomer(OrderProcessResultDto $result): void
    {
        $user = $result->order->user;
        if (! $user) return;

        try {
            $user->notify(new OrderCompletedForCustomerNotification($result));

        } catch (\Throwable $e) {
            Log::channel('stripe')->error("顧客向けメール送信に失敗：order_id={$result->order->id}", [$e]);
        }
    }

    public function notifyOwners(OrderProcessResultDto $result): void
    {
        foreach ($result->shipmentResults as $shipmentResult) {
            $owner = $shipmentResult->owner();
            if (! $owner) continue;

            try {
                $owner->notify(new OrderReceivedForOwnerNotification($result->order, $shipmentResult));

            }catch (\Throwable $e) {
                Log::channel('stripe')->error(
                    'オーナー向けメール送信に失敗', [
                        'message' => $e->getMessage(),
                        'shipment_id' => $shipmentResult->shipment->id,
                        'owner_id' => $owner->id,
                    ]
                );
            }
        }
    }

    public function notifyCheckoutExpired(FailedOrderDto $result): void
    {
        $user = $result->checkoutRequest->user;
        if (! $user) return;

        try {
            $user->notify(new CheckoutExpiredNotification($result));

        } catch (\Throwable $e) {
            Log::channel('stripe')->error("顧客向けメール送信に失敗：chekout_id={$result->checkoutRequest->id}", [$e]);
        }
    }
    public function notifyPaymentFailed(FailedOrderDto $result): void
    {
        $user = $result->checkoutRequest->user;
        if (! $user) return;

        try {
            $user->notify(new PaymentFailedNotification($result));
            
        } catch (\Throwable $e) {
            Log::channel('stripe')->error("顧客向けメール送信に失敗：chekout_id={$result->checkoutRequest->id}", [$e]);
        }
    }
}