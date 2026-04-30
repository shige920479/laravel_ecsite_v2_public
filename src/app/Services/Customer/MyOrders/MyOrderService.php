<?php
namespace App\Services\Customer\MyOrders;

use App\Models\Order;
use App\Services\Customer\MyOrders\DTO\MyOrderQuery;
use Illuminate\Pagination\LengthAwarePaginator;

class MyOrderService implements MyOrderServiceInterface
{
    public function getMyOrders(MyOrderQuery $myOrderQuery): LengthAwarePaginator
    {
        $orders = $myOrderQuery->apply(
            Order::query()
                ->where('user_id', $myOrderQuery->userId)
                ->with(['shipments.orderItems.item.mainImage', 'shipments.shop'])
                ->with(['shipments.orderItems.item' => fn ($query) =>
                    $query->withExists(['reviews as has_review' => fn ($q) =>
                        $q->where('user_id', $myOrderQuery->userId)
                    ])
                ])
        )
        ->latest('ordered_at')
        ->paginate(3)
        ->withQueryString()
        ;

        return $this->markHitItem($orders, $myOrderQuery->keywords);
    }

    private function markHitItem(LengthAwarePaginator $orders, array $keywords): LengthAwarePaginator
    {
        $orders->getCollection()->transform(function ($order) use ($keywords) {
            foreach ($order->shipments as $shipment) {
                foreach ($shipment->orderItems as $orderItem) {
                    $orderItem->is_hit = $this->isHit($orderItem->item_name, $keywords);
                }
            }
            return $order;
        });
        return $orders;
    }

    private function isHit(string $itemName, array $keywords): bool
    {
        if (! $keywords) return false;

        foreach ($keywords as $word) {
            if (! str_contains($itemName, $word)) {
                return false;
            }
        }
        return true;
    }
}