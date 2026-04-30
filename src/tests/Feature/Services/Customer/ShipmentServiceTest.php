<?php

namespace Tests\Feature\Services\Customer;

use App\Models\Cart;
use App\Models\CheckoutItem;
use App\Models\CheckoutRequest;
use App\Models\Item;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\Shop;
use App\Models\User;
use App\Services\Customer\Order\DTO\ShipmentResultDto;
use App\Services\Customer\Shipment\ShipmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShipmentServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function createShipmentAndOrderItem_shipmentsとorderItemsに登録しDTOを返す(): void
    {
        [$user, $checkoutRequest, $checkoutItems, $order] = $this->createShipmentScenario(3);

        $result = (new ShipmentService())->createShipmentAndOrderItem($checkoutRequest, $order);

        $this->assertCount(2, $result);
        $this->assertInstanceOf(ShipmentResultDto::class, $result[0]);
        $counts = $result->map(fn ($dto) => $dto->orderItems->count())->sort()->values();
        $this->assertEquals([1, 2], $counts->toArray());

        $this->assertDatabaseCount('shipments', 2)
            ->assertDatabaseCount('order_items', 3)
            ->assertDatabaseHas('shipments', [
                'order_id' => $order->id,
            ])
            ->assertDatabaseHas('order_items', [
                'shipment_id' => $result[0]->shipment->id,
            ]);
    }

    private function createShipmentScenario(int $count = 3)
    {
        $user = User::factory()->registered()->create();
        $shopA = Shop::factory()->create();
        $shopB = Shop::factory()->create();
        $itemA1 = Item::factory()->for($shopA)->create();
        $itemA2 = Item::factory()->for($shopA)->create();
        $itemB1 = Item::factory()->for($shopB)->create();
        
        Cart::factory()->for($user)->for($itemA1)->create();
        Cart::factory()->for($user)->for($itemA2)->create();
        Cart::factory()->for($user)->for($itemB1)->create();
        $carts = Cart::where('user_id', $user->id)->get();
        
        $checkoutRequest = CheckoutRequest::factory()->for($user)->create();

        $checkoutItems = $carts->map(function ($cart) use ($checkoutRequest) {
            return CheckoutItem::factory()->for($checkoutRequest)->forCart($cart)->create();
        });

        $checkoutRequest->update([
            'total_ex_tax' => $checkoutItems->sum('subtotal_ex_tax'),
            'total_tax' => $checkoutItems->sum('subtotal_tax'),
            'total_in_tax' => $checkoutItems->sum('subtotal_in_tax'),
        ]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'total_ex_tax' => $checkoutRequest->total_ex_tax,
            'total_tax' => $checkoutRequest->total_tax,
            'total_in_tax' => $checkoutRequest->total_in_tax,
        ]);

        return [$user, $checkoutRequest, $checkoutItems, $order];
    }
}	