<?php
namespace App\Services\Customer\Shipment;

use App\Models\CheckoutRequest;
use App\Models\Order;
use Illuminate\Support\Collection;

interface ShipmentServiceInterface
{
    public function createShipmentAndOrderItem(CheckoutRequest $checkoutRequest, Order $order): Collection;
}