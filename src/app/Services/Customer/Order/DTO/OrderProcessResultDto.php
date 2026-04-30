<?php
namespace App\Services\Customer\Order\DTO;

use App\Models\Order;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class OrderProcessResultDto
{
    public function __construct(
        public Order $order,
        public Collection $shipmentResults
    )
    {
    }

    public function shipments(): EloquentCollection
    {
        return $this->shipmentResults->map(fn (ShipmentResultDto $result) => $result->shipment);
    }
}


