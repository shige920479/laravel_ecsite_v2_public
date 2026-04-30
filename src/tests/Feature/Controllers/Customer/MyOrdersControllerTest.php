<?php

namespace Tests\Feature\Controllers\Customer;

use App\Enums\OrderStatus;
use App\Enums\ShippingStatus;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Shipment;
use App\Models\Shop;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

use function PHPUnit\Framework\assertCount;

class MyOrdersControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-01-01 10:00:00'));
    }

    #[Test]
    public function index_ユーザーの購入一覧を検索なしで取得する(): void
    {
        $user = User::factory()->registered()->create();
        $orderedAt = now()->subDays(20);
        $names = ['mug1', 'mug2', 'towel1'];
        $status = ['payment' => OrderStatus::PAID, 'shipment' => ShippingStatus::DELIVERED];
        [$order, $shipments, $orderItems] = $this->createOrder($user, $orderedAt, $names, $status);

        $response = $this->actingAs($user, 'web')->get(route('mypage.orders.index'));

        $response->assertOk()
            ->assertViewIs('user.mypage.orders')
            ->assertViewHas('orders');

        $orders = $response->viewData('orders')->getCollection();

        $this->assertEquals($order->id, $orders->first()->id);
        $this->assertSame($orderItems->first()->item_name,
            $orders->first()->shipments->first()->orderItems->first()->item_name
        );
    }
    #[Test]
    public function index_ユーザーの購入一覧を商品名で検索し取得する(): void
    {
        $user = User::factory()->registered()->create();

        $orderedAt = now()->subDays(20);
        $names = ['test-mugA', 'test-mugA2', 'test-towelA'];
        $status = ['payment' => OrderStatus::PAID, 'shipment' => ShippingStatus::DELIVERED];
        [$orderA, $shipmentsA, $orderItemsA] = $this->createOrder($user, $orderedAt, $names, $status);

        $orderedAt = now()->subDays(10);
        $names = ['test-bottleB', 'test-bottleB2', 'test-cupB'];
        $status = ['payment' => OrderStatus::PAID, 'shipment' => ShippingStatus::DELIVERED];
        [$orderB, $shipmentsB, $orderItemsB] = $this->createOrder($user, $orderedAt, $names, $status);

        $orderedAt = now()->subDays(5);
        $names = ['test-bottleC', 'test-bottleC2', 'test-cupC'];
        $status = ['payment' => OrderStatus::PAID, 'shipment' => ShippingStatus::DELIVERED];
        [$orderC, $shipmentsC, $orderItemsC] = $this->createOrder($user, $orderedAt, $names, $status);

        // 1つ目
        $response = $this->actingAs($user, 'web')->get(route('mypage.orders.index', [
            'keyword' => 'test cup',
        ]));

        $response->assertOk()
            ->assertViewIs('user.mypage.orders')
            ->assertViewHas('orders');

        $orders = $response->viewData('orders')->getCollection();

        $this->assertCount(2, $orders);
        $this->assertCount(6, $orders->pluck('shipments')->flatten()->pluck('orderItems')->flatten());
        $this->assertEquals($orderC->id, $orders->first()->id);
        $this->assertEquals($orderItemsC->first()->id,
            $orders->first()->shipments->first()->orderItems->first()->id
        );

        $orderItems = $orders->first()->shipments->pluck('orderItems')->flatten();
 
        $this->assertTrue($orderItems->firstWhere('item_name', 'test-cupC')->is_hit);
        $this->assertFalse($orderItems->firstWhere('item_name', 'test-bottleC2')->is_hit);

        // 2つ目
        $response = $this->actingAs($user, 'web')->get(route('mypage.orders.index', [
            'keyword' => 'test mug',
        ]));

        $orders = $response->viewData('orders')->getCollection();

        $this->assertCount(1, $orders);
        $this->assertCount(3, $orders->pluck('shipments')->flatten()->pluck('orderItems')->flatten());
        $this->assertEquals($orderA->id, $orders->first()->id);
        $this->assertEquals($orderItemsA->first()->id,
            $orders->first()->shipments->first()->orderItems->first()->id
        );
    }

    #[Test]
    public function index_ユーザーの購入一覧を日付で検索し取得する(): void
    {
        $user = User::factory()->registered()->create();

        $orderedAt = now()->subDays(20);
        $names = ['test-mugA', 'test-mugA2', 'test-towelA'];
        $status = ['payment' => OrderStatus::PAID, 'shipment' => ShippingStatus::DELIVERED];
        [$orderA, $shipmentsA, $orderItemsA] = $this->createOrder($user, $orderedAt, $names, $status);

        $orderedAt = now()->subDays(10);
        $names = ['test-bottleB', 'test-bottleB2', 'test-cupB'];
        $status = ['payment' => OrderStatus::PAID, 'shipment' => ShippingStatus::DELIVERED];
        [$orderB, $shipmentsB, $orderItemsB] = $this->createOrder($user, $orderedAt, $names, $status);

        $orderedAt = now()->subDays(5);
        $names = ['test-bottleC', 'test-bottleC2', 'test-cupC'];
        $status = ['payment' => OrderStatus::PAID, 'shipment' => ShippingStatus::DELIVERED];
        [$orderC, $shipmentsC, $orderItemsC] = $this->createOrder($user, $orderedAt, $names, $status);

        $response = $this->actingAs($user, 'web')->get(route('mypage.orders.index', [
            'keyword' => 'test',
            'from' => now()->subDays(9)->toDateString(),
            'to' => now()->subDays(1)->toDateString()
        ]));

        $orders = $response->viewData('orders')->getCollection();

        $this->assertCount(1, $orders);
        $this->assertCount(3, $orders->pluck('shipments')->flatten()->pluck('orderItems')->flatten());
        $this->assertEquals($orderC->id, $orders->first()->id);
        $this->assertEquals($orderItemsC->first()->id,
            $orders->first()->shipments->first()->orderItems->first()->id
        );

    }
    #[Test]
    public function index_ShippingStatus毎に情報を取得する(): void
    {
        $user = User::factory()->registered()->create();

        $orderedAt = now()->subDays(20);
        $names = ['test-mugA', 'test-mugA2', 'test-towelA'];
        $status = ['payment' => OrderStatus::PAID, 'shipment' => ShippingStatus::DELIVERED];
        [$orderA, $shipmentsA, $orderItemsA] = $this->createOrder($user, $orderedAt, $names, $status);

        $orderedAt = now()->subDays(3);
        $names = ['test-bottleB', 'test-bottleB2', 'test-cupB'];
        $status = ['payment' => OrderStatus::PAID, 'shipment' => ShippingStatus::PREPARING];
        [$orderB, $shipmentsB, $orderItemsB] = $this->createOrder($user, $orderedAt, $names, $status);

        $orderedAt = now()->subDays(1);
        $names = ['test-bottleC', 'test-bottleC2', 'test-cupC'];
        $status = ['payment' => OrderStatus::PAID, 'shipment' => ShippingStatus::UNSHIPPED];
        [$orderC, $shipmentsC, $orderItemsC] = $this->createOrder($user, $orderedAt, $names, $status);

        $response = $this->actingAs($user, 'web')->get(route('mypage.orders.index', [
            'keyword' => 'test',
            'status' => ShippingStatus::UNSHIPPED->value
        ]));

        $orders = $response->viewData('orders')->getCollection();

        $this->assertCount(1, $orders);
        $this->assertEquals($orderC->id, $orders->first()->id);

        $response = $this->actingAs($user, 'web')->get(route('mypage.orders.index', [
            'keyword' => 'test',
            'status' => ShippingStatus::PREPARING->value
        ]));

        $orders = $response->viewData('orders')->getCollection();

        $this->assertCount(1, $orders);
        $this->assertEquals($orderB->id, $orders->first()->id);
    }
    
    #[Test]
    public function index_未ログインであればログイン画面にリダイレクトする(): void
    {
        $response = $this->get(route('mypage.orders.index'));

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function show_オーダー詳細を表示する(): void
    {
        $user = User::factory()->registered()->create();
        $orderedAt = now()->subDays(3);
        $names = ['test-1', 'test-2', 'test-3'];
        $status = ['payment' => OrderStatus::PAID, 'shipment' => ShippingStatus::UNSHIPPED];
        [$orders, $shipments, $orderItems] = $this->createOrder($user, $orderedAt, $names, $status);
        $order = $orders->first();

        $response = $this->actingAs($user, 'web')->get(route('mypage.orders.show', ['order' => $order]));

        $response->assertOk()
            ->assertViewIs('user.mypage.order-show')
            ->assertViewHas('order', fn ($od) =>
                $od->id === $order->id
            );
    }
    #[Test]
    public function show_他人のオーダーは表示されない(): void
    {
        $user = User::factory()->registered()->create();
        $other = User::factory()->registered()->create();
        $orderedAt = now()->subDays(3);
        $names = ['test-1', 'test-2', 'test-3'];
        $status = ['payment' => OrderStatus::PAID, 'shipment' => ShippingStatus::UNSHIPPED];
        [$orders, $shipments, $orderItems] = $this->createOrder($other, $orderedAt, $names, $status);
        $order = $orders->first();

        $response = $this->actingAs($user, 'web')->get(route('mypage.orders.show', ['order' => $order]));

        $response->assertForbidden();
    }


    private function createOrder(User $user, Carbon $orderedAt, array $names, array $status)
    {
        $shopA = Shop::factory()->create();
        $shopB = Shop::factory()->create();
        $itemA1 = Item::factory()->for($shopA)->withMainImage()->create(['name' => $names[0]]);
        $itemA2 = Item::factory()->for($shopA)->withMainImage()->create(['name' => $names[1]]);
        $itemB1 = Item::factory()->for($shopB)->withMainImage()->create(['name' => $names[2]]);

        $order = Order::factory()->for($user)->create([
            'ordered_at' => $orderedAt,
            'payment_status' => $status['payment']
        ]);

        $purchasedItems = collect([$itemA1, $itemA2, $itemB1]);
        $grouped = $purchasedItems->groupBy(fn ($item) => $item->shop_id);

        $shipments = collect();
        $orderItems = collect();
        foreach ($grouped as $shopId => $items) {
            $shipment = Shipment::factory()->for($order)->forUser($user)->create([
                'shop_id' => $shopId,
                'shipping_status' => $status['shipment']
            ]);
            foreach ($items as $item) {
                $orderItem = OrderItem::factory()->for($shipment)->forItem($item, rand(1,3))->create();
                $orderItems->push($orderItem);
            }
            $shipments->push($shipment);
        }

        return [$order, $shipments, $orderItems];
    }
}
