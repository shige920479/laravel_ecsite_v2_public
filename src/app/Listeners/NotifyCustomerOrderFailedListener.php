<?php

namespace App\Listeners;

use App\Events\OrderFailed;
use App\Services\Customer\Order\Notification\OrderNotificationServiceInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyCustomerOrderFailedListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct(private OrderNotificationServiceInterface $orderNotificationService)
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderFailed $event): void
    {
        $this->orderNotificationService->notifyPaymentFailed($event->orderDto);
    }
}
