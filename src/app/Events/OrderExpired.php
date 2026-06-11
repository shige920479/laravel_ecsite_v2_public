<?php

namespace App\Events;

use App\Services\Customer\Order\DTO\FailedOrderDto;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderExpired
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public FailedOrderDto $orderDto)
    {
        //
    }
}
