<?php

namespace App\Listeners;

use App\Events\OrderCompleted;
use App\Services\Customer\Order\Notification\OrderNotificationServiceInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyCustomerOrderCompletedListener implements ShouldQueue
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
    public function handle(OrderCompleted $event): void
    {
        $this->orderNotificationService->notifyCustomer($event->orderDto);
    }
}
