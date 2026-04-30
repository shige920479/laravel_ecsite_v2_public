<?php
namespace App\Services\Customer\Shipment;

use App\Models\CheckoutRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Shipment;
use App\Services\Customer\Order\DTO\ShipmentResultDto;
use App\Services\Customer\Order\DTO\StoreOrderItemCommand;
use App\Services\Customer\Order\DTO\StoreShipmentCommand;
use Illuminate\Support\Collection;

class ShipmentService implements ShipmentServiceInterface
{
    public function createShipmentAndOrderItem(CheckoutRequest $checkoutRequest, Order $order): Collection
    {
        $user = $checkoutRequest->user;
        $groupedByShop = $checkoutRequest->checkoutItems->groupBy('shop_id');

        $results = collect();

        foreach ($groupedByShop as $shopId => $group) {
            $shipment = new Shipment();
            $shipment->fillFromDto(StoreShipmentCommand::createCommand($order->id, $shopId, $user));
            $shipment->save();
            
            $orderItems = collect();

            foreach ($group as $checkoutItem) {
                $orderItem = new OrderItem();
                $orderItem->fillFromDto(StoreOrderItemCommand::createCommand($shipment->id, $checkoutItem));
                $orderItem->save();
                $orderItems->push($orderItem);
            }

            $results->push(new ShipmentResultDto($shipment, $orderItems));
        }

        return $results;
    }
}