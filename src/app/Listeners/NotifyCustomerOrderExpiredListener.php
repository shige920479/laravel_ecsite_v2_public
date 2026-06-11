<?php

namespace App\Listeners;

use App\Events\OrderExpired;
use App\Services\Customer\Order\Notification\OrderNotificationServiceInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyCustomerOrderExpiredListener implements ShouldQueue
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
    public function handle(OrderExpired $event): void
    {
        $this->orderNotificationService->notifyCheckoutExpired($event->orderDto);
    }
}
