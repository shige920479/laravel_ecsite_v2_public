<?php

namespace Tests\Feature\Services\Customer;

use App\Enums\CheckoutStatus;
use App\Models\Cart;
use App\Models\CheckoutItem;
use App\Models\CheckoutRequest;
use App\Models\Item;
use App\Models\Shop;
use App\Models\StockHistory;
use App\Models\User;
use App\Services\Customer\Order\Exceptions\NotFoundCheckoutRequestException;
use App\Services\Customer\Order\Notification\OrderNotificationServiceInterface;
use App\Services\Customer\Order\WebhookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Str;
use Stripe\Event;
use Tests\TestCase;

class WebhookServiceTest extends TestCase
{
    use RefreshDatabase;

    const COMPLETED = 'checkout.session.completed';
    const EXPIRED = 'checkout.session.expired';
    const FAILED = 'payment_intent.payment_failed';

    #[Test]
    public function handle_決済成功時の処理を正しく実行できる(): void
    {
        [$user, $checkoutRequest, $checkoutItems, $itemA1, $itemA2, $itemB1] = $this->createCheckoutScenario(3);
        $event = $this->fakeEvent($checkoutRequest->id, self::COMPLETED);

        $webhookService = app()->make(WebhookService::class);
        $webhookService->handle($event);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total_in_tax' => $checkoutRequest->total_in_tax,
            'stripe_session_id' => $event->data->object->id,
        ])
            ->assertDatabaseCount('shipments', 2)
            ->assertDatabaseCount('order_items', 3)
            ->assertDatabaseHas('checkout_requests', [
                'id' => $checkoutRequest->id,
                'status' => CheckoutStatus::COMPLETED,
            ])
            ->assertDatabaseMissing('carts', [
                'user_id' => $user->id
            ])
            ->assertDatabaseHas('webhook_events', [
                'event_id' => $event->id
            ])
            ;
    }
    #[Test]
    public function handle_checkoutRequestIdがEventオブジェクトになければ何もしない(): void
    {
         [$user, $checkoutRequest, $checkoutItems, $itemA1, $itemA2, $itemB1] = $this->createCheckoutScenario(3);
        $event = $this->fakeEvent(null, self::COMPLETED);

        $mock = Mockery::mock(OrderNotificationServiceInterface::class);
        $mock->shouldReceive('notifyCustomer')->never();
        $mock->shouldReceive('notifyOwners')->never();
        $this->app->instance(OrderNotificationServiceInterface::class, $mock);
        
        $webhookService = app()->make(WebhookService::class);
        $webhookService->handle($event);

        $this->assertDatabaseEmpty('orders');
        $this->assertDatabaseEmpty('shipments');
        $this->assertDatabaseEmpty('order_items');
        $this->assertDatabaseEmpty('webhook_events');
    }

    #[Test]
    public function handle_checkoutRequestIdが存在しなければ例外を投げる(): void
    {
         [$user, $checkoutRequest, $checkoutItems, $itemA1, $itemA2, $itemB1] = $this->createCheckoutScenario(3);
        $event = $this->fakeEvent(999, self::COMPLETED);
        
        $this->expectException(NotFoundCheckoutRequestException::class);
        
        $webhookService = app()->make(WebhookService::class);
        $webhookService->handle($event);
    }

    #[Test]
    public function handle_checkoutRequestIdが存在しなければ処理は実行されない(): void
    {
         [$user, $checkoutRequest, $checkoutItems, $itemA1, $itemA2, $itemB1] = $this->createCheckoutScenario(3);
        $event = $this->fakeEvent(999, self::COMPLETED);

        $webhookService = app()->make(WebhookService::class);
        
        try {
            $webhookService->handle($event);
            $this->fail('例外が発生しませんでした');

        } catch (\Throwable $e) {
            $this->assertDatabaseEmpty('orders')
                ->assertDatabaseEmpty('shipments')
                ->assertDatabaseEmpty('order_items')
                ->assertDatabaseCount('carts', 3)
                ->assertDatabaseHas('checkout_requests', [
                    'id' => $checkoutRequest->id,
                    'status' => CheckoutStatus::PENDING
            ]);
        }
    }
    #[Test]
    public function handle_イベントが2回送られても2重登録を行わない(): void
    {
         [$user, $checkoutRequest, $checkoutItems, $itemA1, $itemA2, $itemB1] = $this->createCheckoutScenario(3);
        $event = $this->fakeEvent($checkoutRequest->id, self::COMPLETED);

        $webhookService = app()->make(WebhookService::class);
        $webhookService->handle($event); // 1回目
        $webhookService->handle($event); // 2回目

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total_in_tax' => $checkoutRequest->total_in_tax,
            'stripe_session_id' => $event->data->object->id,
        ])
            ->assertDatabaseCount('shipments', 2)
            ->assertDatabaseCount('order_items', 3)
            ->assertDatabaseHas('checkout_requests', [
                'id' => $checkoutRequest->id,
                'status' => CheckoutStatus::COMPLETED,
            ])
            ->assertDatabaseMissing('carts', [
                'user_id' => $user->id
            ])
            ->assertDatabaseCount('webhook_events', 1)
            ;
    }
    #[Test]
    public function handle_期限切れイベントでは在庫戻しやステータス変更を行い通知送付(): void
    {
         [$user, $checkoutRequest, $checkoutItems, $itemA1, $itemA2, $itemB1] = $this->createCheckoutScenario();
        $event = $this->fakeEvent($checkoutRequest->id, self::EXPIRED);

        $mock = Mockery::mock(OrderNotificationServiceInterface::class);
        $mock->shouldReceive('notifyCheckoutExpired')->once();
        $this->app->instance(OrderNotificationServiceInterface::class, $mock);

        $webhookService = app()->make(WebhookService::class);
        $webhookService->handle($event);
        
        $this->assertDatabaseCount('webhook_events', 1);
        $this->assertDatabaseHas('webhook_events', [
            'event_id' => $event->id,
        ]);
        $this->assertEquals(10, $itemA1->fresh()->stock_current);
        $this->assertEquals(20, $itemA2->fresh()->stock_current);
        $this->assertEquals(30, $itemB1->fresh()->stock_current);
        $this->assertDatabaseCount('stock_histories', 6);
        $this->assertDatabaseHas('stock_histories', [
            'item_id' => $itemA1->id,
            'stock_diff' => 5
        ]);
        $this->assertDatabaseHas('stock_histories', [
            'item_id' => $itemA2->id,
            'stock_diff' => 6
        ]);
        $this->assertDatabaseHas('stock_histories', [
            'item_id' => $itemB1->id,
            'stock_diff' => 7
        ]);
        $this->assertDatabaseHas('checkout_requests', [
            'id' => $checkoutRequest->id,
            'status' => CheckoutStatus::EXPIRED,
        ]);
    }
    #[Test]
    public function handle_期限切れイベントが2回送られても2重登録を行わない(): void
    {
         [$user, $checkoutRequest, $checkoutItems, $itemA1, $itemA2, $itemB1] = $this->createCheckoutScenario();
        $event = $this->fakeEvent($checkoutRequest->id, self::EXPIRED);

        $mock = Mockery::mock(OrderNotificationServiceInterface::class);
        $mock->shouldReceive('notifyCheckoutExpired')->once();
        $this->app->instance(OrderNotificationServiceInterface::class, $mock);

        $webhookService = app()->make(WebhookService::class);
        $webhookService->handle($event); // 1回目
        $webhookService->handle($event); // 2回目

        $this->assertDatabaseCount('webhook_events', 1);
        $this->assertEquals(10, $itemA1->fresh()->stock_current);
        $this->assertEquals(20, $itemA2->fresh()->stock_current);
        $this->assertEquals(30, $itemB1->fresh()->stock_current);
        $this->assertDatabaseCount('stock_histories', 6);
        $this->assertDatabaseHas('stock_histories', [
            'item_id' => $itemA1->id,
            'stock_diff' => 5
        ]);
        $this->assertDatabaseHas('stock_histories', [
            'item_id' => $itemA2->id,
            'stock_diff' => 6
        ]);
        $this->assertDatabaseHas('stock_histories', [
            'item_id' => $itemB1->id,
            'stock_diff' => 7
        ]);
    }

    #[Test]
    public function handle_決済失敗イベントでは在庫戻しやステータス変更を行い通知送付(): void
    {
         [$user, $checkoutRequest, $checkoutItems, $itemA1, $itemA2, $itemB1] = $this->createCheckoutScenario();
        $event = $this->fakeEvent($checkoutRequest->id, self::FAILED);

        $mock = Mockery::mock(OrderNotificationServiceInterface::class);
        $mock->shouldReceive('notifyPaymentFailed')->once();
        $this->app->instance(OrderNotificationServiceInterface::class, $mock);

        $webhookService = app()->make(WebhookService::class);
        $webhookService->handle($event);
        
        $this->assertDatabaseCount('webhook_events', 1);
        $this->assertDatabaseHas('webhook_events', [
            'event_id' => $event->id,
        ]);
        $this->assertEquals(10, $itemA1->fresh()->stock_current);
        $this->assertEquals(20, $itemA2->fresh()->stock_current);
        $this->assertEquals(30, $itemB1->fresh()->stock_current);
        $this->assertDatabaseCount('stock_histories', 6);
        $this->assertDatabaseHas('stock_histories', [
            'item_id' => $itemA1->id,
            'stock_diff' => 5
        ]);
        $this->assertDatabaseHas('stock_histories', [
            'item_id' => $itemA2->id,
            'stock_diff' => 6
        ]);
        $this->assertDatabaseHas('stock_histories', [
            'item_id' => $itemB1->id,
            'stock_diff' => 7
        ]);
        $this->assertDatabaseHas('checkout_requests', [
            'id' => $checkoutRequest->id,
            'status' => CheckoutStatus::FAILED,
        ]);
    }

    private function createCheckoutScenario()
    {
        $user = User::factory()->registered()->create();
        $shopA = Shop::factory()->create();
        $shopB = Shop::factory()->create();
        $itemA1 = Item::factory()->for($shopA)->create(['name' => 'itemA1' ,'stock_current' => 10]);
        $itemA2 = Item::factory()->for($shopA)->create(['name' => 'itemA2' ,'stock_current' => 20]);
        $itemB1 = Item::factory()->for($shopB)->create(['name' => 'itemB1' ,'stock_current' => 30]);
        $cartA1 = Cart::factory()->for($user)->for($itemA1)->create(['quantity' => 5]);
        $cartA2= Cart::factory()->for($user)->for($itemA2)->create(['quantity' => 6]);
        $cartB1 = Cart::factory()->for($user)->for($itemB1)->create(['quantity' => 7]);
        $carts = Cart::where('user_id', $user->id)
            ->whereIn('id', [$cartA1->id, $cartA2->id, $cartB1->id])->get();
        
        $checkoutRequest = CheckoutRequest::factory()->for($user)->create();

        $checkoutItems = $carts->map(function ($cart) use ($checkoutRequest) {
            return CheckoutItem::factory()->for($checkoutRequest)->forCart($cart)->create();
        });

        $checkoutRequest->update([
            'total_ex_tax' => $checkoutItems->sum('subtotal_ex_tax'),
            'total_tax' => $checkoutItems->sum('subtotal_tax'),
            'total_in_tax' => $checkoutItems->sum('subtotal_in_tax'),
        ]);

        foreach ($checkoutItems as $checkoutItem) {
            // stock_after と itemsテーブルのcurrent_stock はStockHistoryファクトリーで自動計算し修正される
            StockHistory::factory()->create([
                'item_id' => $checkoutItem->item_id,
                'stock_diff' => -$checkoutItem->quantity,
                'reason' => '注文時の在庫確保'
            ]);
        }

        return [$user, $checkoutRequest, $checkoutItems, $itemA1, $itemA2, $itemB1];
    }

    private function fakeEvent(?int $checkoutId, $type): Event
    {
        return Event::constructFrom([
            'id' => 'evt_test_' . Str::random(8),
            'type' => $type,
            'data' => [
                'object' => [
                    'id' => 'stripe-session',
                    'metadata' => [
                        'checkout_id' => $checkoutId,
                    ],
                ],
            ],
        ]);
    }
}
