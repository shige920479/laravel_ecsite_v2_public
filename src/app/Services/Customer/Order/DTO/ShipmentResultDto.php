<?php
namespace App\Services\Customer\Order\DTO;

use App\Models\Owner;
use App\Models\Shipment;
use Illuminate\Support\Collection;

class ShipmentResultDto
{
    public function __construct(
        public Shipment $shipment,
        public Collection $orderItems,
    )
    {
    }

    public function owner(): ?Owner
    {
        return $this->shipment->shop?->owner;
    }
}